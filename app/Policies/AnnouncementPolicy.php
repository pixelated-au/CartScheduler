<?php
/**
 * Project: ${PROJECT_NAME}
 * Owner: Pixelated
 * Copyright: 2022
 */

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnnouncementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Announcement $announcement): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, Announcement $announcement): bool
    {
    }

    public function delete(User $user, Announcement $announcement): bool
    {
    }

    public function restore(User $user, Announcement $announcement): bool
    {
    }

    public function forceDelete(User $user, Announcement $announcement): bool
    {
    }
}
