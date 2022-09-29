<?php

namespace App\Actions;

use App\Models\Location;
use App\Models\Shift;
use Illuminate\Support\Carbon;

class DoShiftReservation
{
    public function execute(Shift $shift, Location $location, int $userId, Carbon $shiftDate): bool
    {
        $assignedUsers = $shift->users;
        // using >= just in case we've had some type of data error
        if ($assignedUsers->count() >= $location->max_volunteers) {
            return false;
        }

        $shift->users()->attach($userId, ['shift_date' => $shiftDate]);

        return true;
    }

}
