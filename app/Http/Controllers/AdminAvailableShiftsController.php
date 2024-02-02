<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class AdminAvailableShiftsController extends Controller
{

    public function __invoke(Request $request, string $shiftDate)
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        try {
            $selectedDate = Carbon::parse($shiftDate)->format('Y-m-d');
        } catch (InvalidFormatException $e) {
            Bugsnag::notifyException($e);
            $selectedDate = Carbon::today()->format('Y-m-d');
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
        ])
            ->where('is_enabled', true)
            ->get();

        return [
            'locations' => LocationResource::collection($locations),
        ];
    }
}
