<?php

namespace App\Http\Controllers;

use App\Actions\GetAvailableShiftsCount;
use App\Actions\GetMaxShiftReservationDateAllowed;
use App\Actions\GetUserShiftsData;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class AvailableShiftsController extends Controller
{
    public function __construct(
        private readonly GetUserShiftsData                 $getUserShiftsData,
        private readonly GetAvailableShiftsCount           $getAvailableShiftsCount,
        private readonly GetMaxShiftReservationDateAllowed $getMaxShiftReservationDateAllowed)
    {
    }

    public function __invoke(Request $request, string $shiftDate)
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        try {
            $selectedDate = Carbon::parse($shiftDate);
        } catch (InvalidFormatException $e) {
            Bugsnag::notifyException($e);
            $selectedDate = Carbon::today();
        }

        // Locations are the list of locations in the 'accordion' menu
        $locations = Location::with([
            'shifts'       => fn(HasMany $query) => $query
                ->where('shifts.is_enabled', true)
                ->where(fn(Builder $query) => $query
                    ->whereNull('shifts.available_from')
                    ->orWhere('shifts.available_from', '<=', $selectedDate)
                )
                ->where(fn(Builder $query) => $query
                    ->whereNull('shifts.available_to')
                    ->orWhere('shifts.available_to', '>=', $selectedDate)
                )
                ->whereRaw("CASE
                                    WHEN DAYOFWEEK('$selectedDate') = 1 THEN shifts.day_sunday
                                    WHEN DAYOFWEEK('$selectedDate') = 2 THEN shifts.day_monday
                                    WHEN DAYOFWEEK('$selectedDate') = 3 THEN shifts.day_tuesday
                                    WHEN DAYOFWEEK('$selectedDate') = 4 THEN shifts.day_wednesday
                                    WHEN DAYOFWEEK('$selectedDate') = 5 THEN shifts.day_thursday
                                    WHEN DAYOFWEEK('$selectedDate') = 6 THEN shifts.day_friday
                                    WHEN DAYOFWEEK('$selectedDate') = 7 THEN shifts.day_saturday
                                    END = 1")
                ->orderBy('shifts.start_time'),
            'shifts.users' => fn(BelongsToMany $query) => $query
                ->where('shift_user.shift_date', '=', $selectedDate)
                ->when($user->is_restricted, fn($query) => $query
                    ->where(fn(Builder $query) => $query
                        ->whereRaw('shift_user.shift_id in (select shift_id from shift_user where user_id = ? AND shift_date = ?)', [$user->id, $selectedDate])
                        ->whereRaw('shift_user.shift_date in (select shift_date from shift_user where user_id = ? AND shift_date = ?)', [$user->id, $selectedDate])
                    )
                )
        ])
            ->where('is_enabled', true)
            ->get()
            ->when($user->is_restricted, fn(Collection $locations) => $locations
                ->filter(fn(Location $location) => $location
                    ->shifts->contains(fn(Shift $shift) => $shift->users->contains(fn(User $shiftUser) => $shiftUser->id === $user->id))
                )
                ->each(fn(Location $location) => $location->shifts = $location->shifts->filter(fn(Shift $shift) => $shift
                        ->users
                        ->isNotEmpty()
                    && $shift->users
                        ->contains(fn(User $shiftUser) => $shiftUser->id === $user->id)))
            );

        $endDate         = $this->getMaxShiftReservationDateAllowed->execute()->format('Y-m-d');
        $startDate       = Carbon::today()->format('Y-m-d');
        $shifts          = $this->getUserShiftsData->execute($startDate, $endDate, $user);
        $freeShiftsCount = $user->is_unrestricted
            ? $this->getAvailableShiftsCount->execute($startDate, $endDate)
            : [];

        return [
            'shifts'             => $shifts,
            'freeShifts'         => $freeShiftsCount,
            'locations'          => LocationResource::collection($locations),
            'maxDateReservation' => $endDate,
        ];
    }
}
