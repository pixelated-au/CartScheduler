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
        $gender = $this->faker->randomElement(['male', 'female']);
        return [
            'uuid'            => $this->faker->uuid(),
            'name'            => $this->faker->name(),
            'email'           => $this->faker->unique()->safeEmail(),
            'role'            => $this->faker->randomElement(['admin', 'user']),
            //'email_verified_at'  => now(),
            'gender'          => $gender,
            'mobile_phone'    => $this->faker->phoneNumber(),
            'year_of_birth'   => $this->faker->numberBetween(1950, date('Y') - 18),
            'marital_status'  => $this->faker->randomElement(['married', 'single', 'divorced', 'separated', 'widowed']),
            'appointment'     => $this->getAppointment($gender),
            'serving_as'      => $this->faker->randomElement(['field missionary', 'special pioneer', 'bethel family member', 'regular pioneer', 'publisher']),
            'is_enabled'      => true,
            'is_unrestricted' => true,
            'password'        => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'  => Str::random(10),
        ];
    }

    public function devUser(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name'        => 'Admin',
                'email'       => 'admin@example.com',
                'role'        => 'admin',
                'gender'      => 'male',
                'appointment' => 'elder'
            ];
        });
    }

    public function adminRoleUser(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'role'        => 'admin',
                'gender'      => 'male',
                'appointment' => 'elder'
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

    public function male(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'gender' => 'male',
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

    protected function getAppointment($gender = 'male'): ?string
    {
        if ($gender !== 'male') {
            return null;
        }
        $appointment = $this->faker->randomElement(['elder', 'ministerial servant', '']);
        if ($appointment === '') {
            return null;
        }
        return $appointment;
    }
}
