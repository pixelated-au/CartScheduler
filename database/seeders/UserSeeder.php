<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->devUser()->create();
        User::factory()->adminRoleUser()->count(3)->create();
        User::factory()->userRoleUser()->count(400)->create();
    }
}
