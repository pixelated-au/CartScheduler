<?php

namespace App\Http\Controllers;

use App\Actions\GetFreeShiftsData;
use App\Actions\GetMaxShiftReservationDateAllowed;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class AvailableShiftsController extends Controller
{
    public function __construct(
        private readonly GetFreeShiftsData                 $getFreeShiftsData,
        private readonly GetMaxShiftReservationDateAllowed $getMaxShiftReservationDateAllowed)
    {
    }

    public function __invoke(Request $request, bool $canViewHistorical = false)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        // TODO This appears to be working but we need to limit the locations if a user is restricted
        // TODO BUT FIRST, SEE HOW EASY IT IS TO INJECT THE USER METADATA INTO THE ROSTERING TABLE
        // TODO AND FILTER BY RESPONSBILE BROTHER, AND BROTHER

        $startDate = ($canViewHistorical
            ? Carbon::today()->subMonths(1)->startOfMonth()
            : Carbon::today())
            ->format('Y-m-d');

        // Locations are the list of locations in the 'accordion' menu
        $locations = Location::with([
            'shifts'       => fn(HasMany $query) => $query
                ->where('shifts.is_enabled', true)
                ->orderBy('shifts.start_time'),
            'shifts.users' => fn(BelongsToMany $query) => $query
                ->where('shift_user.shift_date', '>=', $startDate)
                ->when($user->is_restricted, fn($query) => $query
                    ->where(fn(Builder $query) => $query
                        ->whereRaw('shift_user.shift_id in (select shift_id from shift_user where user_id = ?)', [$user->id])
                        ->whereRaw('shift_user.shift_date in (select shift_date from shift_user where user_id = ?)', [$user->id])
                    )
                )
        ])
            ->where('is_enabled', true)
            ->get();

        $endDate = $this->getMaxShiftReservationDateAllowed->execute()->format('Y-m-d');
        $shifts  = $this->getFreeShiftsData->execute($startDate, $endDate, $user);

        return [
            'shifts'             => $shifts,
            'locations'          => LocationResource::collection($locations),
            'maxDateReservation' => $endDate,
        ];
    }
}
