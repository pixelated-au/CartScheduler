<?php

namespace Database\Factories;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Note, this depends on both a location with shifts and a user being created.
 */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        $shift = Shift::inRandomOrder()->first();

        return [
            'shift_id'                 => $shift->id,
            'report_submitted_user_id' => User::inRandomOrder()->first(['id'])->id,
            'shift_date'               => $shift->available_from
                ? $shift->available_from->addDay()
                : date('Y-m-d', strtotime('tomorrow')),
            'placements_count'         => fake()->numberBetween(0, 10),
            'videos_count'             => fake()->numberBetween(0, 10),
            'requests_count'           => fake()->numberBetween(0, 10),
            'shift_was_cancelled'      => fake()->boolean,
            'comments'                 => fake()->sentence,
        ];
    }
}
