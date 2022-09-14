<?php

namespace App\Actions;

use App\Models\Location;
use App\Models\Shift;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class DoShiftReservation
{
    public function execute(Shift $shift, Location $location, int $userId, Carbon $shiftDate): Response|ErrorApiResource
    {
        $assignedUsers = $shift->users;
        // using >= just in case we've had some type of data error
        if ($assignedUsers->count() >= $location->max_volunteers) {
            return ErrorApiResource::create('No available shifts', ErrorApiResource::CODE_NO_AVAILABLE_SHIFTS, 422);
        }

        $shift->users()->attach($userId, ['shift_date' => $shiftDate]);

        return response(200);
    }

}
