<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_id'   => Location::inRandomOrder()->first()->id,
            'day_monday'    => $this->faker->boolean(),
            'day_tuesday'   => true,
            'day_wednesday' => true,
            'day_thursday'  => true,
            'day_friday'    => true,
            'day_saturday'  => $this->faker->boolean(),
            'day_sunday'    => $this->faker->boolean(),
            'start_time'    => $this->faker->randomElement(['09:00:00', '12:00:00', '15:00:00']),
            'end_time'      => $this->faker->randomElement(['12:00:00', '15:00:00', '18:00:00']),
            'is_enabled'    => true,
        ];
    }
}
