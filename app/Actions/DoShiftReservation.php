<?php

namespace App\Actions;

use App\Exceptions\ShiftAvailabilityException;
use App\Models\Location;
use App\Models\Shift;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DoShiftReservation
{
    public function __construct(private readonly ValidateShiftIsNotFullAction $validateShiftIsNotFullAction)
    {
    }

    public function execute(Shift $shift, Location $location, int $userId, Carbon|\Carbon\Carbon $shiftDate): void
    {
        try {
            $this->validateShiftIsNotFullAction->execute($shift, $shiftDate);
        } catch (ShiftAvailabilityException $e) {
            throw ValidationException::withMessages(['shift' => $e->getMessage()]);
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
    }

}
