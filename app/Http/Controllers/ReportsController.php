<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationAdminResource;
use App\Http\Resources\ReportsResource;
use App\Models\Location;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Reports/List', [
            'locations' => LocationAdminResource::collection(Location::all()),
            'reports'   => ReportsResource::collection(
                Report::with(['shift.location', 'user', 'tags'])
                      ->orderBy('id', 'desc')
                      ->get()
            ),
        ]);
    }

    public function show(Report $report)
    {
    }
}
