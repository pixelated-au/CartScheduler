<?php

namespace App\Http\Controllers;

use App\Actions\ErrorApiResource;
use App\Models\Location;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ToggleShiftReservationController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $this->validate($request, [
            'location'   => ['required', 'integer', 'exists:locations,id'],
            'shift'      => ['required', 'integer', 'exists:shifts,id'],
            'do_reserve' => ['required', 'boolean'],
            'date'       => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:last day of next month'
            ],
        ]);

        $shiftDate = Carbon::parse($data['date']);

        $location = Location::with([
            'shifts'       => fn(HasMany $query) => $query->where('shifts.id', '=', $data['shift']),
            'shifts.users' => fn(BelongsToMany $query) => $query->wherePivot(
                'shift_date', $shiftDate->toDateString()
            )
        ])->findOrFail($data['location']);

        /** @var \App\Models\Shift $shift */
        $shift = $location->shifts->first();

        if ($data['do_reserve']) {
            return $this->doShiftReservation($shift, $location, $request, $shiftDate);
        }

        $shift->users()->detach($request->user()->id);

        return response(200);
    }

    protected function doShiftReservation(Shift $shift, Location $location, Request $request, Carbon $shiftDate):
    ErrorApiResource|Response
    {
        $assignedUsers = $shift->users;

        // using >= just in case we've had some type of data error
        if ($assignedUsers->count() >= $location->max_volunteers) {
            return ErrorApiResource::create(
                'No available shifts',
                ErrorApiResource::CODE_NO_AVAILABLE_SHIFTS,
                422
            );
        }

        $shift->users()->attach($request->user(), ['shift_date' => $shiftDate]);

        return response(200);
    }
}
