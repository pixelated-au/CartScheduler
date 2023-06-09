<?php

namespace Database\Factories;

use App\Models\UserAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class UserAvailabilityFactory extends Factory
{
    protected $model = UserAvailability::class;

    const DAY_AVAILABILITIES = [
        'morning',
        'afternoon',
        'evening',
    ];


    public function definition(): array
    {
        return [
            'user_id'       => $this->faker->randomNumber(),
            'day_monday'    => $this->availability(),
            'day_tuesday'   => $this->availability(),
            'day_wednesday' => $this->availability(),
            'day_thursday'  => $this->availability(),
            'day_friday'    => $this->availability(),
            'num_saturdays' => $this->faker->numberBetween(1, 4),
            'num_sundays'   => $this->faker->numberBetween(1, 4),
            'comments'      => $this->faker->sentence(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ];
    }

    private function availability(): string
    {
        if ($this->faker->boolean(70)) {
            return 'not-available';
        } else {
            if ($this->faker->boolean(10)) {
                return 'full-day';
            }
            if ($this->faker->boolean(10)) {
                $half     = ['half-day'];
                $dayParts = $this->faker->randomElements(
                    self::DAY_AVAILABILITIES, $this->faker->numberBetween(0, 3)
                );
                return implode(',', array_merge($half, $dayParts));
            }
            return implode(
                ',',
                $this->faker->randomElements(
                    self::DAY_AVAILABILITIES, $this->faker->numberBetween(1, 3)
                )
            );
        }
    }
}
