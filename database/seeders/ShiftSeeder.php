<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $days      = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $locations = Location::all();
        foreach ($locations as $location) {
            foreach ($days as $day) {
                Shift::factory()->create([
                    'location_id' => $location->id,
                    'weekday'     => $day,
                    'start_time'  => '09:00:00',
                    'end_time'    => '12:00:00',
                ]);
                Shift::factory()->create([
                    'location_id' => $location->id,
                    'weekday'     => $day,
                    'start_time'  => '12:00:00',
                    'end_time'    => '15:00:00',
                ]);
                Shift::factory()->create([
                    'location_id' => $location->id,
                    'weekday'     => $day,
                    'start_time'  => '15:00:00',
                    'end_time'    => '18:00:00',
                ]);
            }
        }
    }
}
