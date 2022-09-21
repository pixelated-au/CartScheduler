<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class AvailableShiftsForMonthController extends Controller
{
    public function __invoke(Request $request, bool $canViewHistorical = false)
    {
        if (!Auth::user()) {
            abort(403);
        }

        $monthStart = $canViewHistorical
            ? Carbon::today()->subMonths(6)->startOfMonth()
            : Carbon::today();
        $monthEnd   = $monthStart->copy()->addMonth()->endOfMonth();
        $locations  = Location::with([
            'shifts' => function (HasMany $query) {
                $query->where('shifts.is_enabled', true);
                $query->where(function ($query) {
                    $query->whereNull('shifts.available_from');
                    $query->orWhere('shifts.available_from', '<=', Carbon::today());
                });
                $query->where(function ($query) {
                    $query->whereNull('shifts.available_to');
                    $query->orWhere('shifts.available_to', '>=', Carbon::today());
                });
            },
            'shifts.users',
        ])
                              ->where('is_enabled', true)
                              ->get();

        $shifts = DB::query()
                    ->select('shift_user.shift_date',
                        'shift_user.shift_id',
                        'shift_user.user_id as volunteer_id',
                        'shifts.start_time',
                        'shifts.location_id',
                        'shifts.available_from',
                        'shifts.available_to',
                        'locations.max_volunteers')
                    ->from('shift_user')
                    ->join('shifts', 'shift_user.shift_id', '=', 'shifts.id')
                    ->join('locations', 'shifts.location_id', '=', 'locations.id')
                    ->where('shift_date', '>=', $monthStart)
                    ->where('shift_date', '<=', $monthEnd)
                    ->where('shifts.is_enabled', true)
                    ->where(function ($query) {
                        $query->whereNull('shifts.available_from');
                        $query->orWhere('shifts.available_from', '<=', Carbon::today());
                    })
                    ->where(function ($query) {
                        $query->whereNull('shifts.available_to');
                        $query->orWhere('shifts.available_to', '>=', Carbon::today());
                    })
                    ->where('locations.is_enabled', true)
                    ->get()
                    ->map(fn(stdClass $shift) => [
                        'shift_date'     => $shift->shift_date,
                        'shift_id'       => $shift->shift_id,
                        'volunteer_id'   => $shift->volunteer_id,
                        'start_time'     => $shift->start_time,
                        'location_id'    => $shift->location_id,
                        'available_from' => Carbon::parse($shift->available_from),
                        'available_to'   => Carbon::parse($shift->available_to),
                        'max_volunteers' => $shift->max_volunteers,
                    ])
                    ->groupBy(['shift_date', 'shift_id'])
                    ->sortKeys();

        return [
            'shifts'    => $shifts,
            'locations' => LocationResource::collection($locations)
        ];
    }
}
