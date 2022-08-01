<?php
/**
 * Project: ${PROJECT_NAME}
 * Owner: Pixelated
 * Copyright: 2022
 */

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Reservation $reservation): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, Reservation $reservation): bool
    {
    }

    public function delete(User $user, Reservation $reservation): bool
    {
    }

    public function restore(User $user, Reservation $reservation): bool
    {
    }

    public function forceDelete(User $user, Reservation $reservation): bool
    {
    }
}
