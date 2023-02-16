<?php

namespace App\Http\Controllers;

use App\Actions\GetFreeShiftsData;
use App\Actions\GetMaxShiftReservationDateAllowed;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailableShiftsForMonthController extends Controller
{
    public function __construct(
        private readonly GetFreeShiftsData $getFreeShiftsData,
        private readonly GetMaxShiftReservationDateAllowed $getMaxShiftReservationDateAllowed)
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

        $endDate = $this->getMaxShiftReservationDateAllowed->execute()->format('Y-m-d');
        $shifts  = $this->getFreeShiftsData->execute($monthStart, $endDate);

        return [
            'shifts'    => $shifts,
            'locations' => LocationResource::collection($locations),
        ];
    }
}
