<?php

namespace App\Http\Controllers;

use App\Actions\GetFreeShiftsData;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailableShiftsForMonthController extends Controller
{
    private GetFreeShiftsData $getFreeShiftsData;

    public function __construct(GetFreeShiftsData $getFreeShiftsData)
    {
        $this->getFreeShiftsData = $getFreeShiftsData;
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
        $shifts = $this->getFreeShiftsData->execute($monthStart);

        return [
            'shifts'    => $shifts,
            'locations' => LocationResource::collection($locations)
        ];
    }
}
