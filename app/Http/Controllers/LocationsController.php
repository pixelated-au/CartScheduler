<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationAdminResource;
use App\Models\Location;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class LocationsController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Location::class, 'location');
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Locations/List', [
            'locations' => LocationAdminResource::collection(Location::with('shifts')->get()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Locations/Add');
    }

    public function store(CreateLocationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        DB::beginTransaction();
        $location = Location::create($data);
        $shifts   = $request->validated('shifts');
        foreach ($shifts as $shift) {
            $shiftModel = new Shift();
            $shiftModel->fill($shift);
            $shiftModel->location_id = $location->id;
            $shiftModel->save();
        }
        DB::commit();

        session()->flash('flash.banner', "Location $location->name successfully created.");
        session()->flash('flash.bannerStyle', 'success');

        return Redirect::route('admin.locations.edit', $location);
    }

    public function edit(Location $location): Response
    {
        return Inertia::render('Admin/Locations/Edit', [
            'location' => LocationAdminResource::make($location->load([
                'shifts' => function ($query) {
                    $query->orderBy('start_time', 'asc');
                }
            ])),
        ]);
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        DB::beginTransaction();
        $shifts = $request->validated('shifts');
        foreach ($shifts as $shift) {
            if (isset($shift['id'])) {
                $shiftModel = Shift::find($shift['id']);
                if (!$shiftModel) {
                    throw new RuntimeException("Shift with an ID of {$shift['id']} belonging to $location->name not found");
                }
            } else {
                $shiftModel = new Shift();
            }
            $shiftModel->fill($shift);
            $shiftModel->save();
        }
        $location->update($request->validated());
        DB::commit();

        return Redirect::route('admin.locations.edit', $location);
    }

    public function destroy(Location $location): RedirectResponse
    {
        $name = $location->name;
        $location->delete();

        session()->flash('flash.banner', "Location $name successfully deleted.");
        session()->flash('flash.bannerStyle', 'danger');

        return Redirect::route('admin.locations.index');
    }
}
