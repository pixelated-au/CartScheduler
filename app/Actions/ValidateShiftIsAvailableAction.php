<?php

namespace App\Actions;

use App\Exceptions\ShiftAvailabilityException;
use App\Models\Shift;
use Illuminate\Support\Carbon;

class ValidateShiftIsAvailableAction
{
    public function execute(Shift $shift, Carbon $date): void
    {
        if ($shift->available_from || $shift->available_to) {
            if ($shift->available_from && $date->isBefore($shift->available_from)) {
                throw ShiftAvailabilityException::notAvailableYet();
            }

            if ($shift->available_to && $date->isAfter($shift->available_to)) {
                throw ShiftAvailabilityException::notAvailableAnymore();
            }
        }
    }
}
