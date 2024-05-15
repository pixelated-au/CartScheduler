<?php

namespace App;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SetUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_see_set_password_page(): void
    {
        $user        = User::factory()->enabled()->state(['password' => null])->create();
        $hashedEmail = base64_encode(Hash::make($user->uuid . $user->email));

        $this->get("/set-password/$user->id/$hashedEmail")
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Profile/SetPassword')
                ->has('editUser', fn(AssertableInertia $data) => $data
                    ->where('id', $user->id)
                    ->where('name', $user->name)
                )
            )
            ->assertOk();
    }

    public function test_user_with_set_password_is_directed_to_login_page(): void
    {
        $user = User::factory()->enabled()->state(['password' => 'password'])->create();

        $this->get("/set-password/$user->id/mock-text")
            ->assertRedirect('/login');
    }

    public function test_logged_in_user_is_directed_to_home_page(): void
    {
        $user = User::factory()->enabled()->state(['password' => 'password'])->create();

        $this->actingAs($user)
            ->get("/set-password/$user->id/mock-text")
            ->assertRedirect('/');
    }

    public function test_visitor_has_invalid_hashed_email(): void
    {
        $user = User::factory()->enabled()->state(['password' => null])->create();

        $this->getJson("/set-password/$user->id/mock-text")
            ->assertNotFound();
    }

    public function test_visitor_can_set_password(): void
    {
        $user        = User::factory()->enabled()->state(['password' => null])->create();
        $hashedEmail = base64_encode(Hash::make($user->uuid . $user->email));

        $this->post("/set-password", [
            'password_confirmation' => 'password',
            'password'              => 'password',
            'hashed_email'          => $hashedEmail,
            'user_id'               => $user->id,
        ])
            ->assertRedirect('/login')
            ->assertSessionHas('flash.setPassword', "Your password has been set. Please use it to log in.");

        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_set_password_validation_is_working(): void
    {
        $this->post("/set-password", [
            'password_confirmation' => 'abc123',
            'password'              => 'password',
        ])
            ->assertRedirect()
            ->assertInvalid([
                'password',
                'hashed_email' => $this->makeErrorCode(100),
                'user_id'      => $this->makeErrorCode(300),
            ]);

        $this->post("/set-password", [
            'password_confirmation' => 'password',
            'password'              => 'password',
            'hashed_email'          => 123456789,
            'user_id'               => 999999999,
        ])
            ->assertRedirect()
            ->assertInvalid([
                'hashed_email' => $this->makeErrorCode(200), 'user_id' => $this->makeErrorCode(500),
            ]);

        $this->post("/set-password", [
            'password'     => 'password',
            'hashed_email' => 'xyz',
            'user_id'      => 'hi',
        ])
            ->assertRedirect()
            ->assertInvalid(['password_confirmation', 'password', 'user_id' => $this->makeErrorCode(400)]);
    }

    public function test_invalid_hashed_email_fails_properly(): void
    {
        $user = User::factory()->enabled()->state(['password' => null])->create();

        $this->post("/set-password", [
            'password_confirmation' => 'password',
            'password'              => 'password',
            'hashed_email'          => 'mock-text',
            'user_id'               => $user->id,
        ])
            ->assertNotFound();
    }

    private function makeErrorCode(int|string $code): string
    {
        return Str::of(config('app.key'))
            ->pipe(fn(string $key) => sha1($key . $code))
            ->substr(0, 8)
            ->wrap('(code ', ')')
            ->value();
    }
}
