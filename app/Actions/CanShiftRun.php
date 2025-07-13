<?php

namespace App\Actions;

use App\Models\Location;
use Illuminate\Support\Collection;

class CanShiftRun
{
    public static function CanShiftRun(int $shiftId, Collection $targetShiftUsers) : bool
    {
        $static = new static();
        $shiftData = $static->getShiftRequirements($shiftId);

        $min_volunteers = $shiftData[0]->min_volunteers;
        $max_volunteers = $shiftData[0]->max_volunteers;
        $requires_brother = $shiftData[0]->requires_brother;

        if ($targetShiftUsers->count() > $max_volunteers || $targetShiftUsers->count() < $min_volunteers) {
            return false;
        }

        if ($requires_brother) {
            if ($targetShiftUsers->
                where(function ($shiftUser) {
                        return ($shiftUser->gender == "male");
                    }
                )
            ) {
                return true;
            }
            else
                return false;
        }

        return true;
    }

    private function getShiftRequirements(int $locationId) {
        return Location::select(['id', 'name', 'min_volunteers', 'max_volunteers', 'requires_brother'])
            ->where('id', '=', $locationId)
            ->get();
    }


}
