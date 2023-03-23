<?php

namespace App\Actions;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GetAvailableUsersForShift
{
    public function execute(Shift $shift, string $date): Collection
    {
        $overlappingShifts = $this->getOverlappingShifts($shift, $date);

        return User::query()
            ->distinct()
            ->select('users.*')
            ->leftJoin(table: 'shift_user', first: 'users.id', operator: '=', second: 'shift_user.user_id')
            ->where('users.is_enabled', true)
            ->whereDoesntHave('bookings', fn(Builder $query) => $query
                ->join(table: 'shifts', first: 'shift_user.shift_id', operator: '=', second: 'shifts.id')
                ->join(table: 'locations', first: 'shifts.location_id', operator: '=', second: 'locations.id')
                ->where('shift_date', $date)
                ->where('shifts.is_enabled', true)
                ->where('locations.is_enabled', true)
                ->whereIn('shift_id', $overlappingShifts)
            )
            ->get();
    }

    private function getOverlappingShifts(Shift $shift, string $date): \Illuminate\Support\Collection
    {
        return Shift::query()
            ->select(['id'])
            ->where($this->getDayOfWeekForDate($date), true)
            ->where('start_time', '<', $shift->end_time)
            ->where('end_time', '>', $shift->start_time)
            ->get()
            ->map(fn(Shift $shift) => $shift->getKey());
    }

    /**
     * format of lowercase 'L' ('l') returns the lowercase full day name of the week in English
     */
    private function getDayOfWeekForDate(string $date): string
    {
        return 'day_' . strtolower(date('l', strtotime($date)));
    }
}
