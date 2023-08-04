<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::unsetEventDispatcher();// stops the UserAccountCreated mail from being sent
        User::factory()->devUser()->create();
        User::factory()->adminRoleUser()->count(3)->create();
        User::factory()->userRoleUser()->count(50)->create();
    }
}
