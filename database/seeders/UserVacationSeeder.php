<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserVacation;
use Illuminate\Database\Seeder;

class UserVacationSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(
            fn(User $user) => UserVacation::factory()
                ->when(fn() => $user->id === 1 || rand(0, 9) >= 8) // 20% chance
                ->create(['user_id' => $user->id])
        );
    }
}
