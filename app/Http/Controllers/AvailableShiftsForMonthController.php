<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AvailableShiftsForMonthController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->validations($request);

        $year       = Carbon::now()->year;
        $month      = Carbon::now()->month;
        $monthStart = Carbon::create($year, $month);
        if ($monthStart->isPast()) {
            $monthStart = Carbon::today();
        }
        $monthEnd = $monthStart->copy()->addMonth()->endOfMonth();

        $locations = Location::with([
            'shifts.users'
        ])->get();

        $shifts = DB::query()
                    ->select('shift_user.shift_date',
                        'shift_user.shift_id',
                        'shift_user.user_id as volunteer_id',
                        'shifts.start_time',
                        'shifts.location_id',
                        'locations.max_volunteers')
                    ->from('shift_user')
                    ->join('shifts', 'shift_user.shift_id', '=', 'shifts.id')
                    ->join('locations', 'shifts.location_id', '=', 'locations.id')
                    ->where('shift_date', '>=', $monthStart)
                    ->where('shift_date', '<=', $monthEnd)
                    ->where('shifts.is_enabled', true)
                    ->where('locations.is_enabled', true)
                    ->get()
                    ->groupBy(['shift_date', 'shift_id'])
                    ->sortKeys();

        return ['shifts' => $shifts, 'locations' => LocationResource::collection($locations)];
    }

    protected function validations(Request $request): void
    {
        //$date      = Carbon::now();
        //$thisMonth = $date->month;
        //$thisYear  = $date->year;
        //$date->addMonth();
        //$nextMonth = $date->month;
        //$nextYear  = $date->year;
        //$request->validate([
        //    'month' => ['integer', "between:$thisMonth,$nextMonth"],
        //    'year'  => ['integer', "between:$thisYear,$nextYear"],
        //]);
        if (!Auth::user()) {
            abort(403);
        }
    }
}
