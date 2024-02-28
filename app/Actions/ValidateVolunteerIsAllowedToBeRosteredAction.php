<?php

namespace App\Actions;

use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Collection;

class ValidateVolunteerIsAllowedToBeRosteredAction
{
    // TODO when updating to PHP 8.2, change the return type to `true|string`
    /**
     * @return true|string
     */
    public function execute(Location $location, User $user, Collection $currentVolunteers): bool | string
    {
        if ($location->requires_brother && $user->gender === 'female') {
            $hasBro = $currentVolunteers->contains(fn(User $existingVolunteer) => $existingVolunteer->gender === 'male');

            if (!$hasBro && $currentVolunteers->count() >= $location->max_volunteers - 1) {
                return 'Sorry, the last volunteer for this shift needs to be a brother';
            }
        }
        return true;
    }
}
