<?php

namespace App\Http\Controllers;

use App\Actions\GetOutstandingReportCount;
use App\Actions\GetShiftFilledData;
use App\Data\FilledShiftData;
use App\Enums\CacheKey;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function __invoke(GetShiftFilledData $shiftFilledData, GetOutstandingReportCount $getOutstandingReportCount)
    {
        $totalUsers         = Cache::flexibleWithEnum(CacheKey::TotalUsers, [7200, 10800], static fn() => User::all()->count());
        $totalLocations     = Cache::flexibleWithEnum(CacheKey::TotalLocations, [7200, 10800], static fn() => Location::all()->count());
        $shiftFilledData    = Cache::flexibleWithEnum(CacheKey::ShiftFilledData, [7200, 10800],
            static fn() => FilledShiftData::collect($shiftFilledData->execute('fortnight')));
        $outstandingReports = Cache::flexibleWithEnum(CacheKey::OutstandingReports, [7200, 10800],
            static fn() => $getOutstandingReportCount->execute());

        return Inertia::render('Admin/Dashboard', [
            'totalUsers'         => $totalUsers,
            'totalLocations'     => $totalLocations,
            'shiftFilledData'    => $shiftFilledData,
            'outstandingReports' => $outstandingReports,
        ]);
    }
}
