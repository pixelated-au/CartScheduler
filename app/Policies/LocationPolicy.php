<?php
/**
 * Project: ${PROJECT_NAME}
 * Owner: Pixelated
 * Copyright: 2022
 */

namespace App\Policies;

use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Location $location): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, Location $location): bool
    {
    }

    public function delete(User $user, Location $location): bool
    {
    }

    public function restore(User $user, Location $location): bool
    {
    }

    public function forceDelete(User $user, Location $location): bool
    {
    }
}
