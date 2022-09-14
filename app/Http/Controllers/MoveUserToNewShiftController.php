<?php

namespace App\Http\Controllers;

use App\Actions\DoShiftReservation;
use App\Actions\ErrorApiResource;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MoveUserToNewShiftController extends Controller
{
    public function __invoke(Request $request, DoShiftReservation $doShiftReservation)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'location_id'  => 'required|exists:locations,id',
            'old_shift_id' => 'required|exists:shifts,id',
            'date'         => 'required|date',
        ]);

        $oldShift  = Shift::find($request->get('old_shift_id'));
        $date      = Carbon::parse($request->get('date'));
        $startTime = $oldShift->start_time;

        $location = Location::with([
            'shifts'       => fn(HasMany $query) => $query->where('shifts.start_time', '=', $oldShift->start_time),
            'shifts.users' => fn(BelongsToMany $query) => $query->wherePivot(
                'shift_date', $date->toDateString()
            )
        ])
                            ->where('id', $request->get('location_id'))
                            ->where('is_enabled', true)
                            ->first();

        if (!$location) {
            return ErrorApiResource::create('Location not found',
                ErrorApiResource::CODE_LOCATION_NOT_FOUND, 422);
        }

        $shift = $location->shifts->firstWhere('start_time', $startTime);
        if (!$shift) {
            return ErrorApiResource::create('No shift found for this location at this time',
                ErrorApiResource::CODE_SHIFT_NOT_FOUND, 422);
        }

        if ($shift->available_from || $shift->available_to) {
            if ($shift->available_from && $date->isBefore($shift->available_from)) {
                return ErrorApiResource::create('Shift is not available yet',
                    ErrorApiResource::CODE_SHIFT_NOT_AVAILABLE_YET, 422);
            }
            if ($shift->available_to && $date->isAfter($shift->available_to)) {
                return ErrorApiResource::create('Shift is not available anymore',
                    ErrorApiResource::CODE_SHIFT_NO_LONGER_AVAILABLE, 422);
            }
        }

        $user = User::find($request->get('user_id'));
        if ($location->requires_brother && $user->gender === 'female') {
            $shiftVolunteers = $location->shifts->first()->users;
            $hasBro          = $shiftVolunteers->contains(fn($volunteer) => $volunteer->gender === 'male');

            if (!$hasBro && $shiftVolunteers->count() >= $location->max_volunteers - 1) {
                return ErrorApiResource::create('Sorry, the last volunteer for this shift needs to be a brother',
                    ErrorApiResource::CODE_BROTHER_REQUIRED, 422);
            }
        }

        return DB::transaction(
            static function () use ($date, $location, $shift, $doShiftReservation, $request, $oldShift) {
                $oldShift->users()->detach($request->get('user_id'));

                return $doShiftReservation->execute($shift, $location, $request->get('user_id'), $date);
            });
    }
}
