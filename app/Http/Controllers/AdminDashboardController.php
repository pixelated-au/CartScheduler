<?php

namespace App\Http\Controllers;

use App\Actions\GetOutstandingReports;
use App\Actions\GetShiftFilledData;
use App\Enums\Role;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function __invoke(GetShiftFilledData $shiftFilledData, GetOutstandingReports $getOutstandingReports)
    {
        // Not checking for admin role because the routes should check for it

        return Inertia::render('Admin/Dashboard', [
            'totalUsers'         => User::all()->count(),
            'totalLocations'     => Location::all()->count(),
            'shiftFilledData'    => $shiftFilledData->execute('fortnight'),
            'outstandingReports' => $getOutstandingReports->execute(),
        ]);
    }
}
