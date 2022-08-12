<?php

namespace App\Http\Controllers;

use App\Actions\ErrorApiResource;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ToggleShiftReservationController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $this->getValidated($request);

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

    protected function getValidated(Request $request): array
    {
        return $this->validate($request, [
            'location'   => ['required', 'integer', 'exists:locations,id'],
            'shift'      => [
                'required',
                'integer',
                'exists:shifts,id',
                function ($attribute, $value, $fail) use ($request) {
                    $data = $request->all();
                    if ($data['do_reserve']) {
                        $shiftDate           = Carbon::parse($data['date']);
                        $currentShiftsOnDate = ShiftUser::with(['shift', 'shift.location'])
                                                        ->where('user_id', $request->user()->id)
                                                        ->where('shift_date', $shiftDate->toDateString())
                                                        ->get();
                        // only validate if adding a shift
                        $theShift   = Shift::find($data['shift']);
                        $shiftStart = $theShift->start_time;
                        $shiftEnd   = $theShift->end_time;
                        $foundMatch = $currentShiftsOnDate->first(fn(ShiftUser $currentShift) => $currentShift->shift->start_time >= $shiftStart && $currentShift->shift->end_time <= $shiftEnd);
                        if ($foundMatch) {
                            $fail("Sorry, You already have another shift at this time on this day at {$foundMatch->shift->location->name}.");
                        }
                    }
                }
            ],
            'do_reserve' => ['required', 'boolean'],
            'date'       => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:last day of next month',
            ],
        ]);
    }
}
