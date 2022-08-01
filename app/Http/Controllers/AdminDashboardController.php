<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Admin/Dashboard', [
            'totalUsers'     => User::all()->count(),
            'totalLocations' => Location::all()->count(),
        ]);
    }
}
