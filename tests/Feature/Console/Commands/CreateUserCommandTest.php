<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\CreateUserCommand;
use App\Mail\UserAccountCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_command_can_create_a_user(): void
    {
        $userData = [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'phone'    => '1234567890',
            'gender'   => 'male',
            'password' => 'password',
        ];
        $this->assertDatabaseCount('users', 0);
        $this->artisan(CreateUserCommand::class, $userData)
            ->expectsOutput('User Test User created successfully')
            ->assertExitCode(0);

        $userData['mobile_phone'] = $userData['phone'];
        $userData['role']         = 'admin';
        unset($userData['phone'], $userData['password']);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', $userData);
    }

    public function test_create_user_command_sends_email_verification(): void
    {
        $userData = [
            'name'   => 'MailTest User',
            'email'  => 'mailtest@example.com',
            'phone'  => '1234567890',
            'gender' => 'male',
        ];
        Mail::fake();
        $this->assertDatabaseCount('users', 0);
        $this->artisan(CreateUserCommand::class, $userData)
            ->expectsOutput('User MailTest User created successfully')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name'  => 'MailTest User',
            'email' => 'mailtest@example.com',
            'role'  => 'admin'
        ]);
        Mail::assertSent(UserAccountCreated::class, 1);
    }

    public function test_create_multiple_users(): void
    {
        User::factory()->enabled()->adminRoleUser()->create();

        $userData = [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'phone'    => '1234567890',
            'gender'   => 'male',
            'password' => 'password',
        ];

        $this->assertDatabaseCount('users', 1);

        $this->artisan(CreateUserCommand::class, $userData)
            ->expectsOutput('Only one user can be created from this interface')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['name' => 'Test User', 'email' => 'test@example.com']);
    }
}
