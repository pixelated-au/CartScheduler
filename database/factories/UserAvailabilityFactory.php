<?php

namespace Database\Factories;

use App\Enums\AvailabilityHours;
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
        $this->dayParts = collect(AvailabilityHours::cases());

        $monday    = $this->availability();
        $tuesday   = $this->availability();
        $wednesday = $this->availability();
        $thursday  = $this->availability();
        $friday    = $this->availability();
        $saturday  = $this->availability();
        $sunday    = $this->availability();

        return [
            'user_id'        => $this->faker->randomNumber(),
            'day_monday'     => $monday,
            'day_tuesday'    => $tuesday,
            'day_wednesday'  => $wednesday,
            'day_thursday'   => $thursday,
            'day_friday'     => $friday,
            'day_saturday'   => $saturday,
            'day_sunday'     => $sunday,
            'num_mondays'    => $monday ? $this->faker->numberBetween(0, 4) : 0,
            'num_tuesdays'   => $tuesday ? $this->faker->numberBetween(0, 4) : 0,
            'num_wednesdays' => $wednesday ? $this->faker->numberBetween(0, 4) : 0,
            'num_thursdays'  => $thursday ? $this->faker->numberBetween(0, 4) : 0,
            'num_fridays'    => $friday ? $this->faker->numberBetween(0, 4) : 0,
            'num_saturdays'  => $saturday ? $this->faker->numberBetween(0, 4) : 0,
            'num_sundays'    => $sunday ? $this->faker->numberBetween(0, 4) : 0,
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
                ->filter(function (AvailabilityHours $availability) {
                    return $availability->value >= 7 && $availability->value <= 18;
                })
                ->toArray();
        }

        // 10% chance of morning
        if ($this->faker->boolean(20)) {
            return $this->dayParts
                ->filter(fn(AvailabilityHours $availability) => $availability->value >= 7 && $availability->value <= 12)
                ->toArray();
        }

        // 10% chance of afternoon
        if ($this->faker->boolean(20)) {
            return $this->dayParts
                ->filter(fn(AvailabilityHours $availability) => $availability->value >= 12 && $availability->value <= 18)
                ->toArray();
        }
        return null;
    }
}
