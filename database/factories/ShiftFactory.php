<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_id' => Location::inRandomOrder()->first()->id,
            'weekday'     => $this->faker->randomElement([
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday'
            ]),
            'start_time'  => $this->faker->randomElement(['09:00:00', '12:00:00', '15:00:00']),
            'end_time'    => $this->faker->randomElement(['12:00:00', '15:00:00', '18:00:00']),
            'is_enabled'  => true,
        ];
    }
}
