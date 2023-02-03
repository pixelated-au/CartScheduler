<?php

namespace Database\Seeders\Tests;

use App\Models\Location;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class LocationAndShiftsSeeder extends Seeder
{
    public function run(): void
    {
        $location = Location::factory()->create([
            'requires_brother' => true,
        ]);
        Shift::factory()->everyDay()->create([
            'location_id' => $location->id,
        ]);
    }
}
