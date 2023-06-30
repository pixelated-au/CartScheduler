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
            'min_volunteers' => 3,
            'max_volunteers' => 3,
        ]);
        Shift::factory()->everyDay()->create([
            'location_id' => $location->id,
        ]);
    }
}
