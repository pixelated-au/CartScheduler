<?php

namespace App\Http\Controllers;

use App\Actions\GetFreeShiftsData;
use App\Actions\GetMaxShiftReservationDateAllowed;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailableShiftsController extends Controller
{
    public function __construct(
        private readonly GetFreeShiftsData                 $getFreeShiftsData,
        private readonly GetMaxShiftReservationDateAllowed $getMaxShiftReservationDateAllowed)
    {
    }

    public function __invoke(Request $request, bool $canViewHistorical = false)
    {
        if (!Auth::user()) {
            abort(403);
        }

        $startDate = ($canViewHistorical
            ? Carbon::today()->subMonths(1)->startOfMonth()
            : Carbon::today())
            ->format('Y-m-d');

        // Locations are the list of locations in the 'accordion' menu
        $locations = Location::with([
            'shifts' => fn(HasMany $query) => $query
                ->where('shifts.is_enabled', true)
                ->orderBy('shifts.start_time'),
            'shifts.users' => fn(BelongsToMany $query) => $query
                ->where('shift_user.shift_date', '>=', $startDate)
        ])
            ->where('is_enabled', true)
            ->get();

        $endDate = $this->getMaxShiftReservationDateAllowed->execute()->format('Y-m-d');
        $shifts  = $this->getFreeShiftsData->execute($startDate, $endDate);

        return [
            'shifts'             => $shifts,
            'locations'          => LocationResource::collection($locations),
            'maxDateReservation' => $endDate,
        ];
    }
}
