<?php

namespace App\Actions;

use App\Models\Location;
use App\Models\Shift;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DoShiftReservation
{
    public function execute(Shift $shift, Location $location, int $userId, Carbon|\Carbon\Carbon $shiftDate): bool
    {
        $assignedUsers = $shift->users;
        // using >= just in case we've had some type of data error
        if ($assignedUsers->count() >= $location->max_volunteers) {
            return false;
        }

        $shift->users()->attach($userId, ['shift_date' => $shiftDate]);
        activity()
            ->performedOn($shift)
            ->causedBy(Auth::user())
            ->withProperties([
                'user_id'             => $userId,
                'shift_date'          => $shiftDate,
                'shift.location.name' => $location->name,
            ])
            ->log('shift_reserved');

        return true;
    }

}
