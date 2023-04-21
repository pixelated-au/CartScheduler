<?php

namespace App\Actions;

use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GetAvailableUsersForShift
{
    public function execute(Shift $shift, string $date): Collection
    {
        $overlappingShifts = $this->getOverlappingShifts($shift, $date);

        $canOnlyBrothersRegister = $this->canOnlyBrothersBook($shift, $date);
        return User::query()
            ->distinct()
            ->select(['users.*', 'last_shift_date', 'last_shift_start_time'])
            ->leftJoinSub(
                query: DB::query()
                    ->select(['user_id'])
                    ->selectRaw('MAX(shift_date) as last_shift_date')
                    ->selectRaw('MAX(shifts.start_time) as last_shift_start_time')
                    ->from('shift_user')
                    ->join(table: 'shifts', first: 'shift_user.shift_id', operator: '=', second: 'shifts.id')
                    ->groupBy('user_id'),
                as: 'last_shift',
                first: 'last_shift.user_id',
                operator: '=',
                second: 'users.id')
            ->where('users.is_enabled', true)
            ->whereDoesntHave('bookings', fn(Builder $query) => $query
                ->join(table: 'shifts', first: 'shift_user.shift_id', operator: '=', second: 'shifts.id')
                ->join(table: 'locations', first: 'shifts.location_id', operator: '=', second: 'locations.id')
                ->where('shift_date', $date)
                ->where('shifts.is_enabled', true)
                ->where('locations.is_enabled', true)
                ->whereIn('shift_id', $overlappingShifts)
            )
            ->when($canOnlyBrothersRegister, fn(Builder $query) => $query
                ->where('users.gender', 'male')
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

    private function canOnlyBrothersBook(Shift $shift, string $date): bool
    {
        $location = $shift->load('location')->location;
        $location = $shift->load('location')->location;
        if (!$location->requires_brother) {
            return false;
        }

        $sistersReservedCount = ShiftUser::with('user')
            ->whereRelation(
                'user',
                fn(Builder $query) => $query->where('gender', '=', 'female')
            )
            ->where('shift_id', $shift->id)
            ->where('shift_date', $date)
            ->count();
        if ($sistersReservedCount < $location->max_volunteers - 1) {
            return false;
        }

        return true;
    }

    /**
     * format of lowercase 'L' ('l') returns the lowercase full day name of the week in English
     */
    private function getDayOfWeekForDate(string $date): string
    {
        return 'day_' . strtolower(date('l', strtotime($date)));
    }
}
