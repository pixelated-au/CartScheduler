<?php

namespace App\Actions;

use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetAvailableUsersForShift
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    /** @noinspection UnknownColumnInspection */
    public function execute(Shift $shift, Carbon $date, bool $showAll): Collection
    {
        $overlappingShifts = $this->getOverlappingShifts($shift, $date);

        $canOnlyBrothersRegister = $this->canOnlyBrothersBook($shift, $date);

        return User::query()
            ->distinct()
            ->select(['users.*', 'last_shift_date', 'last_shift_start_time'])
            ->when($this->settings->enableUserAvailability, fn(Builder $query) => $query
                ->addSelect(['filled_sundays', 'filled_mondays', 'filled_tuesdays', 'filled_wednesdays', 'filled_thursdays', 'filled_fridays', 'filled_saturdays'])
                ->tap(fn(Builder $query) => $this->getDayCounts($query, $date)))
            ->addSelect(['num_sundays', 'num_mondays', 'num_tuesdays', 'num_wednesdays', 'num_thursdays', 'num_fridays', 'num_saturdays'])
            ->leftJoin(table: 'user_availabilities', first: 'users.id', operator: '=', second: 'user_availabilities.user_id')
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
                ->when($canOnlyBrothersRegister, fn(Builder $query) => $query
                    ->where('users.gender', 'male')
                )
            )
            ->when($this->settings->enableUserAvailability && !$showAll, fn(Builder $query) => $query
//                ->join(table: 'user_availabilities', first: 'users.id', operator: '=', second: 'user_availabilities.user_id')
                ->leftJoin(table: 'user_vacations', first: 'users.id', operator: '=', second: 'user_vacations.user_id')
                ->tap(fn(Builder $query) => $this->queryIsAvailableOnDayOfWeek($query, $date))
                ->tap(fn(Builder $query) => $this->queryIsAvailableAtHour($query, $date, $shift->start_hour))
                ->tap(fn(Builder $query) => $this->queryIsAvailableAtHour($query, $date, $shift->end_hour))
                ->withCount(['vacations as vacations_count' => fn(Builder $query) => $query
                    ->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                ])
                ->having('vacations_count', '=', 0)
            )
            ->get();
    }

    private function queryIsAvailableOnDayOfWeek(Builder $query, string $date): void
    {
        /** @noinspection SpellCheckingInspection */
        $query->whereRaw("CASE
                                    WHEN DAYOFWEEK('$date') = 1 THEN user_availabilities.num_sundays
                                    WHEN DAYOFWEEK('$date') = 2 THEN user_availabilities.num_mondays
                                    WHEN DAYOFWEEK('$date') = 3 THEN user_availabilities.num_tuesdays
                                    WHEN DAYOFWEEK('$date') = 4 THEN user_availabilities.num_wednesdays
                                    WHEN DAYOFWEEK('$date') = 5 THEN user_availabilities.num_thursdays
                                    WHEN DAYOFWEEK('$date') = 6 THEN user_availabilities.num_fridays
                                    WHEN DAYOFWEEK('$date') = 7 THEN user_availabilities.num_saturdays
                                    END > 0");
    }

    private function queryIsAvailableAtHour(Builder $query, string $date, int $hour): void
    {
        /** @noinspection SpellCheckingInspection */
        $query->whereRaw("FIND_IN_SET($hour, CASE
                                    WHEN DAYOFWEEK('$date') = 1 THEN user_availabilities.day_sunday
                                    WHEN DAYOFWEEK('$date') = 2 THEN user_availabilities.day_monday
                                    WHEN DAYOFWEEK('$date') = 3 THEN user_availabilities.day_tuesday
                                    WHEN DAYOFWEEK('$date') = 4 THEN user_availabilities.day_wednesday
                                    WHEN DAYOFWEEK('$date') = 5 THEN user_availabilities.day_thursday
                                    WHEN DAYOFWEEK('$date') = 6 THEN user_availabilities.day_friday
                                    WHEN DAYOFWEEK('$date') = 7 THEN user_availabilities.day_saturday
                                    END)");
    }

    private function getOverlappingShifts(Shift $shift, Carbon $date): \Illuminate\Support\Collection
    {
        return Shift::query()
            ->select(['id'])
            ->where($this->getDayOfWeekForDate($date), true)
            ->where('start_time', '<', $shift->end_time)
            ->where('end_time', '>', $shift->start_time)
            ->get()
            ->map(fn(Shift $shift) => $shift->getKey());
    }

    private function getDayCounts(Builder $query, Carbon $date): Builder
    {
        collect([
            'sunday'    => 1,
            'monday'    => 2,
            'tuesday'   => 3,
            'wednesday' => 4,
            'thursday'  => 5,
            'friday'    => 6,
            'saturday'  => 7,
        ])->each(fn(int $dayNumber, string $dayName) => $query->leftJoinSub(
            query: $this->getDayCountQuery($date, $dayName, $dayNumber),
            as: "{$dayName}_shifts",
            first: "{$dayName}_shifts.user_id",
            operator: '=',
            second: 'users.id'));
        return $query;
    }

    /**
     * This query is used to get the number of days per week a user has a shift
     * Note, it's not counting the number of shifts per day because it's possible for a user to have multiple shifts on
     * the same day, but it counts the number of shifts per day of the week
     */
    private function getDayCountQuery(Carbon $date, string $dayName, int $dayNumber): \Illuminate\Database\Query\Builder
    {
        return DB::query()
            ->selectRaw("user_id, COUNT(*) as filled_{$dayName}s")
            ->fromSub(fn(\Illuminate\Database\Query\Builder $query) => $query
                ->selectRaw("user_id, 1")
                ->from('shift_user')
                ->join(table: 'shifts', first: 'shift_user.shift_id', operator: '=', second: 'shifts.id')
                ->join(table: 'locations', first: 'shifts.location_id', operator: '=', second: 'locations.id')
                ->whereBetween('shift_date', [$date->clone()->startOfMonth(), $date->clone()->endOfMonth()])
                ->whereRaw('DAYOFWEEK(shift_date) = ?', $dayNumber)
                ->where('shifts.is_enabled', true)
                ->where('locations.is_enabled', true)
                ->groupBy('user_id', 'shift_date')
                , as: "filled_{$dayName}")
            ->groupBy('user_id');
    }

    private function canOnlyBrothersBook(Shift $shift, Carbon $date): bool
    {
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
    private function getDayOfWeekForDate(Carbon $date): string
    {
        return 'day_' . strtolower($date->format('l'));
    }
}
