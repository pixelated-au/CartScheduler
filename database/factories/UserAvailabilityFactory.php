<?php

namespace Database\Factories;

use App\Enums\AvailabilityPeriods;
use App\Models\UserAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UserAvailabilityFactory extends Factory
{
    protected $model = UserAvailability::class;

    protected Collection $dayParts;

    public function definition(): array
    {
        $this->dayParts = collect(AvailabilityPeriods::getHourParts());

        return [
            'user_id'        => $this->faker->randomNumber(),
            'day_monday'     => $this->availability(),
            'day_tuesday'    => $this->availability(),
            'day_wednesday'  => $this->availability(),
            'day_thursday'   => $this->availability(),
            'day_friday'     => $this->availability(),
            'day_saturday'   => $this->availability(),
            'day_sunday'     => $this->availability(),
            'num_mondays'    => $this->faker->numberBetween(0, 4),
            'num_tuesdays'   => $this->faker->numberBetween(0, 4),
            'num_wednesdays' => $this->faker->numberBetween(0, 4),
            'num_thursdays'  => $this->faker->numberBetween(0, 4),
            'num_fridays'    => $this->faker->numberBetween(0, 4),
            'num_saturdays'  => $this->faker->numberBetween(0, 4),
            'num_sundays'    => $this->faker->numberBetween(0, 4),
            'comments'       => $this->faker->sentence(),
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ];
    }

    private function availability(): ?array
    {
        // 10% chance of full-day
        if ($this->faker->boolean(20)) {
            return $this->dayParts
                ->filter(function (AvailabilityPeriods $availability) {
                    return (int)$availability->value >= 7 && (int)$availability->value <= 18;
                })
                ->toArray();
        }

        // 10% chance of morning
        if ($this->faker->boolean(20)) {
            return $this->dayParts
                ->filter(fn(AvailabilityPeriods $availability) => (int)$availability->value >= 7 && (int)$availability->value <= 12)
                ->toArray();
        }

        // 10% chance of afternoon
        if ($this->faker->boolean(20)) {
            return $this->dayParts
                ->filter(fn(AvailabilityPeriods $availability) => (int)$availability->value >= 12 && (int)$availability->value <= 18)
                ->toArray();
        }
        return null;
    }
}
