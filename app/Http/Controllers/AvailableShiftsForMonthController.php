<?php

namespace App\Http\Controllers;

use App\Actions\GetFreeShiftsData;
use App\Enums\DBPeriod;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailableShiftsForMonthController extends Controller
{
    public function __construct(private readonly GetFreeShiftsData $getFreeShiftsData)
    {
    }

    public function __invoke(Request $request, bool $canViewHistorical = false)
    {
        if (!Auth::user()) {
            abort(403);
        }

        $monthStart = ($canViewHistorical
            ? Carbon::today()->subMonths(6)->startOfMonth()
            : Carbon::today())
            ->format('Y-m-d');

        // Locations are the list of locations in the 'accordion' menu
        $locations = Location::with([
            'shifts' => function (HasMany $query) {
                $query->where('shifts.is_enabled', true);
                $query->orderBy('shifts.start_time');
            },
            'shifts.users',
        ])
                             ->where('is_enabled', true)
                             ->get();

        // Shifts are the locked in shifts for all users. In the future, it may be worth considering caching this...

        $period               = DBPeriod::getConfigPeriod();
        $duration             = config('cart-scheduler.shift_reservation_duration');
        $releaseShiftsOnDay   = config('cart-scheduler.release_weekly_shifts_on_day');
        $doReleaseShiftsDaily = config('cart-scheduler.do_release_shifts_daily');

        $shifts = $this->getFreeShiftsData->execute($monthStart,
            $period,
            $duration,
            $releaseShiftsOnDay,
            $doReleaseShiftsDaily,
        );

        ray($shifts);

        return [
            'shifts'    => $shifts,
            'locations' => LocationResource::collection($locations),
        ];
    }
}
