<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationChoiceResource;
use App\Http\Resources\ReportTagResource;
use App\Models\Location;
use Spatie\Tags\Tag;

class GetUserLocationChoicesController extends Controller
{
    public function __invoke()
    {
        $locations = Location::where('is_enabled', true)->orderBy('name')->get();
        return LocationChoiceResource::collection($locations);
    }
}
