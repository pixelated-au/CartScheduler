<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'uuid'           => $this->faker->uuid(),
            'name'           => $this->faker->name(),
            'email'          => $this->faker->unique()->safeEmail(),
            'role'           => $this->faker->randomElement(['admin', 'user']),
            //'email_verified_at'  => now(),
            'gender'         => $this->faker->randomElement(['male', 'female']),
            'mobile_phone'   => $this->faker->phoneNumber(),
            'is_enabled'     => true,
            'password'       => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function devUser(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name'  => 'Admin',
                'email' => 'admin@example.com',
                'role'  => 'admin',
            ];
        });
    }

    public function adminRoleUser(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'role'   => 'admin',
                'gender' => 'male',
            ];
        });
    }

    public function userRoleUser(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'role'       => 'user',
                'is_enabled' => random_int(0, 9) !== 9,
            ];
        });
    }

    public function female(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'gender' => 'female',
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return Factory
     */
    public function unverified(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Indicate that the user should have a personal team.
     *
     * @return $this
     */
}
