<?php

namespace App\Http\Controllers;

use App\Data\LocationAdminData;
use App\Data\ReportsData;
use App\Models\Location;
use App\Models\Report;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Reports/List', [
            'locations' => LocationAdminData::collect(Location::with('shifts')->get()),
            'reports'   => ReportsData::collect(
                Report::query()
                    ->with(['shift.location', 'user', 'tags'])
                    ->orderBy('id', 'desc')
                    ->get()
            ),
        ]);
    }
}
