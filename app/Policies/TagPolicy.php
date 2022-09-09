<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Tags\Tag;

class TagPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->role === Role::Admin->value;
    }

    public function view(User $user, Tag $tag): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Admin->value;
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->role === Role::Admin->value;
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->role === Role::Admin->value;
    }

    public function restore(User $user, Tag $tag): bool
    {
        return $user->role === Role::Admin->value;
    }

    public function forceDelete(User $user, Tag $tag): bool
    {
        return $user->role === Role::Admin->value;
    }
}
