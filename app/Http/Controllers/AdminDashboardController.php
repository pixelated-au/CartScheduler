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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role !== Role::Admin->value) {
            abort(403);
        }

        return Inertia::render('Admin/Dashboard', [
            'totalUsers'         => User::all()->count(),
            'totalLocations'     => Location::all()->count(),
            'shiftFilledData'    => $shiftFilledData->execute('fortnight'),
            'outstandingReports' => $getOutstandingReports->execute(),
        ]);
    }
}
