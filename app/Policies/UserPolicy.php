<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->role === Role::Admin->value;
    }

    public function view(User $user, User $model): bool
    {
        if ($user->role === Role::Admin->value) {
            return true;
        }
        if ($user->id === $model->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Admin->value;
    }

    public function update(User $user, User $model): bool
    {
        if ($user->role === Role::Admin->value) {
            return true;
        }
        if ($user->id === $model->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false; // cannot delete oneself
        }

        return $user->role === Role::Admin->value;
    }

    public function restore(User $user, User $model): void
    {
        //
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false; // cannot delete oneself
        }

        return $user->role === Role::Admin->value;
    }
}
