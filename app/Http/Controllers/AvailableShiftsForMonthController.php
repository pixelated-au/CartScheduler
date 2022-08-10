<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailableShiftsForMonthController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->validations($request);

        $month = $request->input('month');
        $year  = $request->input('year');
        if (!$year) {
            $year = Carbon::now()->year;
        }
        if (!$month) {
            $month = Carbon::now()->month;
        }
        $monthStart = Carbon::create($year, $month);
        $monthEnd   = $monthStart->copy()->endOfMonth();

        //$locations = Location::with(['shifts.users'])->get();
        $locations = Location::with([
            //'shifts' => function (HasMany $query) use ($monthEnd, $monthStart) {
            //    $query->where(function ($query) use ($monthEnd, $monthStart) {
            //        $query->whereNull('shifts.available_from')
            //              ->orWhere('shifts.available_from', '>=', $monthStart);
            //    });
            //    $query->where(function ($query) use ($monthEnd, $monthStart) {
            //        $query->whereNull('shifts.available_to')
            //              ->orWhere('shifts.available_to', '<=', $monthEnd);
            //    });
            //    //$query->whereNull('shift.available_from')
            //    //      ->orWhere('shifts.available_from', '>=', $monthStart)
            //    //      ->whereNull('shifts.available_to', '<=', $monthEnd)
            //    //      ->orWhere('shifts.available_to', '<=', $monthEnd);
            //},
            'shifts.users'
        ])->get();

        return LocationResource::collection($locations);
    }

    protected function validations(Request $request): void
    {
        $date      = Carbon::now();
        $thisMonth = $date->month;
        $thisYear  = $date->year;
        $date->addMonth();
        $nextMonth = $date->month;
        $nextYear  = $date->year;
        $request->validate([
            'month' => ['integer', "between:$thisMonth,$nextMonth"],
            'year'  => ['integer', "between:$thisYear,$nextYear"],
        ]);
        if (!Auth::user()) {
            abort(403);
        }
    }
}
