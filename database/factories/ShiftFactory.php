<?php

namespace Database\Factories;

use App\Models\Location;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ShiftFactory extends Factory
{
    public function definition(): array
    {
        $available = CarbonPeriod::between(
            $this->faker->dateTimeBetween('now', '+1 month'),
            $this->faker->dateTimeBetween('+1 month', '+2 months'),
        );

        $startTime = Carbon::now()->setTimeFromTimeString($this->faker->randomElement(['9:00', '12:00', '15:00']));

        return [
            'location_id'    => Location::inRandomOrder()?->first()?->id,
            'day_monday'     => $this->faker->boolean(),
            'day_tuesday'    => true,
            'day_wednesday'  => true,
            'day_thursday'   => true,
            'day_friday'     => true,
            'day_saturday'   => $this->faker->boolean(),
            'day_sunday'     => $this->faker->boolean(),
            'start_time'     => $startTime->format('H:i:s'),
            'end_time'       => $startTime->addHours(3)->format('H:i:s'),
            'available_from' => $available->first(),
            'available_to'   => $available->last(),
            'is_enabled'     => true,
        ];
    }

    public function everyDay(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'day_monday'     => true,
            'day_tuesday'    => true,
            'day_wednesday'  => true,
            'day_thursday'   => true,
            'day_friday'     => true,
            'day_saturday'   => true,
            'day_sunday'     => true,
            'available_from' => null,
            'available_to'   => null,
        ]);
    }

    public function everyDay9am(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'is_enabled'     => true,
            'day_monday'     => true,
            'day_tuesday'    => true,
            'day_wednesday'  => true,
            'day_thursday'   => true,
            'day_friday'     => true,
            'day_saturday'   => true,
            'day_sunday'     => true,
            'start_time'     => '09:00:00',
            'end_time'       => '12:00:00',
            'available_from' => null,
            'available_to'   => null,
        ]);
    }

    public function everyDay1230pm(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'is_enabled'     => true,
            'day_monday'     => true,
            'day_tuesday'    => true,
            'day_wednesday'  => true,
            'day_thursday'   => true,
            'day_friday'     => true,
            'day_saturday'   => true,
            'day_sunday'     => true,
            'start_time'     => '12:30:00',
            'end_time'       => '15:30:00',
            'available_from' => null,
            'available_to'   => null,
        ]);
    }
}
