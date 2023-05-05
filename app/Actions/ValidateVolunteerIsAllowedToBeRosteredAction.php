<?php

namespace App\Actions;

use App\Exceptions\VolunteerIsAllowedException;
use App\Models\Location;
use App\Models\User;

class ValidateVolunteerIsAllowedToBeRosteredAction
{
    public function execute(Location $location, User $user): void
    {
        if ($location->requires_brother && $user->gender === 'female') {
            $shiftVolunteers = $location->shifts->first()->load('users')->users;
            $hasBro          = $shiftVolunteers->contains(fn($volunteer) => $volunteer->gender === 'male');

            if (!$hasBro && $shiftVolunteers->count() >= $location->max_volunteers - 1) {
                throw VolunteerIsAllowedException::brotherRequired();
            }
        }
    }
}
