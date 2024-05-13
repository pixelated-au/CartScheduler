<?php /** @noinspection PhpUnusedParameterInspection */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'             => $this->faker->city(),
            'description'      => $this->faker->sentences(asText: true),
            'min_volunteers'   => $this->faker->randomElement(['1', '2', '3', '4', '5']),
            'max_volunteers'   => 5,
            'requires_brother' => $this->faker->boolean(),
            'latitude'         => (string)$this->faker->randomFloat(6, -36, -37),
            'longitude'        => (string)$this->faker->randomFloat(6, 143, 144),
            'is_enabled'       => true,
        ];
    }

    public function threeVolunteers(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'max_volunteers' => 3,
            ];
        });
    }

    public function requiresBrother(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'requires_brother' => true,
            ];
        });
    }

    public function allPublishers(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'requires_brother' => false,
            ];
        });
    }
}
