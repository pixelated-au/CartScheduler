<?php

namespace App\Actions;

use App\Exceptions\VolunteerIsAllowedException;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Collection;

class ValidateVolunteerIsAllowedToBeRosteredAction
{
    public function execute(Location $location, User $user, Collection $currentVolunteers): void
    {
        if ($location->requires_brother && $user->gender === 'female') {
            $hasBro = $currentVolunteers->contains(fn(User $existingVolunteer) => $existingVolunteer->gender === 'male');

            if (!$hasBro && $currentVolunteers->count() >= $location->max_volunteers - 1) {
                throw VolunteerIsAllowedException::brotherRequired();
            }
        }
    }
}
