<?php
/**
 * Project: ${PROJECT_NAME}
 * Owner: Pixelated
 * Copyright: 2022
 */

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShiftPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Shift $shift): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, Shift $shift): bool
    {
    }

    public function delete(User $user, Shift $shift): bool
    {
    }

    public function restore(User $user, Shift $shift): bool
    {
    }

    public function forceDelete(User $user, Shift $shift): bool
    {
    }
}
