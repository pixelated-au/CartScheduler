<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationAdminResource;
use App\Http\Resources\ReportsResource;
use App\Models\Location;
use App\Models\Report;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Reports/List', [
            'locations.shifts' => LocationAdminResource::collection(Location::all()),
            'reports'          => ReportsResource::collection(
                Report::with(['shift:start_time' => ['location:id,name'], 'user:id,name,gender,mobile_phone,email', 'tags:name'])
                      ->where('shift_date', '>=', now()->subMonths(2))
                      ->orderBy('id', 'desc')
                      ->get()
            ),
        ]);
    }
}
