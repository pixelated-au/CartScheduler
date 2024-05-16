<?php

namespace App\Http\Controllers;

use App\Actions\GetAvailableShiftsCount;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminAvailableShiftsController extends Controller
{
    public function __construct(private readonly GetAvailableShiftsCount $getAvailableShiftsCount)
    {
    }

    public function __invoke(Request $request, string $shiftDate)
    {
        try {
            $selectedDate = Carbon::parse($shiftDate);
        } catch (InvalidFormatException $e) {
            Bugsnag::notifyException($e);
            $selectedDate = Carbon::today();
        }
        $formattedDate = $selectedDate->format('Y-m-d');

        // Locations are the list of locations in the 'accordion' menu
        $locations = Location::with([
            'shifts'       => fn(HasMany $query) => $query
                ->where('shifts.is_enabled', true)
                ->where(fn(Builder $query) => $query
                    ->whereNull('shifts.available_from')
                    ->orWhere('shifts.available_from', '<=', $formattedDate)
                )
                ->where(fn(Builder $query) => $query
                    ->whereNull('shifts.available_to')
                    ->orWhere('shifts.available_to', '>=', $formattedDate)
                )
                ->whereRaw("CASE
                                    WHEN DAYOFWEEK('$formattedDate') = 1 THEN shifts.day_sunday
                                    WHEN DAYOFWEEK('$formattedDate') = 2 THEN shifts.day_monday
                                    WHEN DAYOFWEEK('$formattedDate') = 3 THEN shifts.day_tuesday
                                    WHEN DAYOFWEEK('$formattedDate') = 4 THEN shifts.day_wednesday
                                    WHEN DAYOFWEEK('$formattedDate') = 5 THEN shifts.day_thursday
                                    WHEN DAYOFWEEK('$formattedDate') = 6 THEN shifts.day_friday
                                    WHEN DAYOFWEEK('$formattedDate') = 7 THEN shifts.day_saturday
                                    END = 1")
                ->orderBy('shifts.start_time'),
            'shifts.users' => fn(BelongsToMany $query) => $query
                ->where('shift_user.shift_date', '=', $selectedDate)
        ])
            ->where('is_enabled', true)
            ->get();

        $selectedDate->startOfMonth();
        $endDate = $selectedDate->clone()->endOfMonth();

        $freeShiftsCount = $this->getAvailableShiftsCount->execute($selectedDate->format('Y-m-d'), $endDate->format('Y-m-d'));

        return [
            'freeShifts' => $freeShiftsCount,
            'locations'  => LocationResource::collection($locations),
        ];
    }
}
