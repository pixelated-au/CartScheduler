<?php

namespace Database\Factories;

use App\Models\Location;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    public function definition(): array
    {
        $available = CarbonPeriod::between(
            $this->faker->dateTimeBetween('now', '+1 month'),
            $this->faker->dateTimeBetween('+1 month', '+2 months'),
        );

        return [
            'location_id'    => Location::inRandomOrder()->first()->id,
            'day_monday'     => $this->faker->boolean(),
            'day_tuesday'    => true,
            'day_wednesday'  => true,
            'day_thursday'   => true,
            'day_friday'     => true,
            'day_saturday'   => $this->faker->boolean(),
            'day_sunday'     => $this->faker->boolean(),
            'start_time'     => $this->faker->randomElement(['09:00:00', '12:00:00', '15:00:00']),
            'end_time'       => $this->faker->randomElement(['12:00:00', '15:00:00', '18:00:00']),
            'available_from' => $available->first(),
            'available_to'   => $available->last(),
            'is_enabled'     => true,
        ];
    }

    public function everyDay(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'day_monday'     => true,
                'day_tuesday'    => true,
                'day_wednesday'  => true,
                'day_thursday'   => true,
                'day_friday'     => true,
                'day_saturday'   => true,
                'day_sunday'     => true,
                'available_from' => null,
                'available_to'   => null,
            ];
        });
    }

}
