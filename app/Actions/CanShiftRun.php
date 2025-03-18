<?php

namespace App\Actions;

use App\Models\User;
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
            //dd("min: $min_volunteers, max: $max_volunteers, requires_brother: $requires_brother, name: {$shiftData[0]->name}");
            dump("not enough volunteers ({$targetShiftUsers->count()} vs $max_volunteers vs $min_volunteers)");
            return false;
        }

        if ($requires_brother) {
            if ($targetShiftUsers->
                where(function ($shiftUser) {
                        return ($shiftUser->gender == "male");
                    }
                )
            ) {
                dump("requires brother - found");
                return true;
            }
            else
                dump("requires brother - not found");
                return false;
        }

        dump("final true");
        return true;
    }

    private function getShiftRequirements(int $locationId) {
        return Location::select(['id', 'name', 'min_volunteers', 'max_volunteers', 'requires_brother'])
            ->where('id', '=', $locationId)
            ->get();
    }


}
