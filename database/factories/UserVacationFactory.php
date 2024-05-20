<?php

namespace Database\Factories;

use App\Enums\AvailabilityHours;
use App\Models\UserAvailability;
use App\Models\UserVacation;
use Carbon\CarbonImmutable;
use DateInterval;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UserVacationFactory extends Factory
{
    protected $model = UserVacation::class;

    protected Collection $dayParts;

    /**
     * @throws \Exception
     */
    public function definition(): array
    {
        $startDate = CarbonImmutable::instance($this->faker->dateTimeBetween('now', '+6 months'));
        return [
            'start_date'  => $startDate,
            'end_date'    => $startDate->add($this->faker->numberBetween(1, 30), 'day'),
            'description' => $this->faker->sentence(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];
    }
}
