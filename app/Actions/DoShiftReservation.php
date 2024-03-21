<?php

namespace App\Actions;

use App\Models\Location;
use App\Models\Shift;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DoShiftReservation
{
    public function __construct(private readonly ValidateShiftIsNotFullAction $validateShiftIsNotFullAction)
    {
    }

    public function execute(Shift $shift, Location $location, int $userId, Carbon|\Carbon\Carbon $shiftDate): void
    {
        $this->validateShiftIsNotFullAction->execute($shift, $shiftDate);

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
    }

}
