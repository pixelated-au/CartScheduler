<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationsController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Location::class, 'location');
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Users/List', [
            'locations' => Location::all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Locations/Add');
    }

    public function store(Request $request)
    {
    }

    public function show(Location $location)
    {
    }

    public function edit(Location $location)
    {
    }

    public function update(Request $request, Location $location)
    {
    }

    public function destroy(Location $location)
    {
    }
}
