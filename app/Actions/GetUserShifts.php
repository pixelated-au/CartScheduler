<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetUserShifts
{
    public function execute(Carbon $targetDate, ?int $userId = null): Collection
    {
        return User::with(['shifts.location' => fn(BelongsTo $query) => $query->select('id', 'name')])
            ->shiftsOnDate($targetDate, 'start_time', 'end_time')
            ->select(['id', 'name', 'email', 'gender'])
            ->when(
                is_int($userId),
                fn(Builder $query) => $query->where('users.id', '=', $userId)
            )
            ->get();
    }
}
