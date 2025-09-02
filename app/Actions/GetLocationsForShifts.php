<?php

namespace App\Actions;

use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetLocationsForShifts
{
    public function execute(string $date, ?User $user = null, ?array $userFields = null): Collection
    {
        return Location::query()
            ->with([
                    'shifts'       => fn(HasMany $query) => $query
                        ->where('shifts.is_enabled', true)
                        ->where(fn(Builder $query) => $query
                            ->whereNull('shifts.available_from')
                            ->orWhere('shifts.available_from', '<=', $date)
                        )
                        ->where(fn(Builder $query) => $query
                            ->whereNull('shifts.available_to')
                            ->orWhere('shifts.available_to', '>=', $date)
                        )
                        ->whereRaw("CASE
                                    WHEN DAYOFWEEK('$date') = 1 THEN shifts.day_sunday
                                    WHEN DAYOFWEEK('$date') = 2 THEN shifts.day_monday
                                    WHEN DAYOFWEEK('$date') = 3 THEN shifts.day_tuesday
                                    WHEN DAYOFWEEK('$date') = 4 THEN shifts.day_wednesday
                                    WHEN DAYOFWEEK('$date') = 5 THEN shifts.day_thursday
                                    WHEN DAYOFWEEK('$date') = 6 THEN shifts.day_friday
                                    WHEN DAYOFWEEK('$date') = 7 THEN shifts.day_saturday
                                    END = 1")
                        ->orderBy('shifts.start_time'),
                    'shifts.users' => fn(BelongsToMany $query) => $query
                        ->where('shift_user.shift_date', '=', $date)
                        ->when($userFields, fn(Builder $query) => $query->select($this->mapUserFields($userFields)))
                        ->when($user?->is_restricted, fn(Builder $query) => $query
                            ->where(fn(Builder $query) => $query
                                ->whereRaw('shift_user.shift_id in (select shift_id from shift_user where user_id = ? AND shift_date = ?)',
                                    [$user->id, $date])
                                ->whereRaw('shift_user.shift_date in (select shift_date from shift_user where user_id = ? AND shift_date = ?)',
                                    [$user->id, $date])
                            )
                        )
                ]
            )
            ->where('locations.is_enabled', true)
            ->get()
            ->when(
            // If the user is restricted, then...
                value: $user?->is_restricted,
                callback: fn(Collection $locations) => $locations
                    // Filter out the locations that don't have a shift for the user
                    ->filter(
                        fn(Location $location) => $location->shifts->contains(
                            fn(Shift $shift) => $shift->users->contains(
                                fn(User $shiftUser) => $shiftUser->id === $user->id
                            )
                        )
                    )
                    // Filter out the shifts that the user isn't rostered on to
                    ->each(
                        fn(Location $location) => $location->shifts = $location->shifts->filter(
                            fn(Shift $shift) => $shift->users->isNotEmpty()
                                && $shift->users->contains(fn(User $shiftUser) => $shiftUser->id === $user->id)
                        )
                    )
            )
            ->each(
                fn(Location $location) => $location->shifts->each(
                    fn(Shift $shift) => $shift->users->map(
                        fn(User $user) => $user->id = null
                    )
                )
            );
    }

    /**
     * @return string[]
     */
    protected function mapUserFields(array $userFields): array
    {
        $mapped = Arr::map($userFields, static function (string $item) {
            if (Str::startsWith($item, 'users.')) {
                return $item;
            }

            return "users.$item";
        });

        if (!Arr::exists($mapped, 'users.id')) {
            $mapped[] = 'users.id';
        }

        return $mapped;
    }
}
