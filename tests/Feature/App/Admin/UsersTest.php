<?php

namespace Tests\Feature\App\Admin;

use App\Mail\UserAccountCreated;
use App\Models\Location;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_new_user_and_user_receives_email(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $this->assertDatabaseCount('users', 1);
        $user = User::factory()->enabled()->makeOne();
        $this->assertDatabaseCount('users', 1);

        $userData = $user->toArray();

        Mail::fake();
        $this->actingAs($admin)
            ->postJson("/admin/users/", $userData)
            ->assertRedirect()
            ->assertSessionHas('flash.banner', "User $user->name successfully created.");
        Mail::assertSent(UserAccountCreated::class, 1);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_admin_adds_new_user_with_bad_data_fails(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $this->assertDatabaseCount('users', 1);

        $userData = [
            'name'                => '',
            'email'               => 'invalid email',
            'role'                => 'invalid role',
            'gender'              => 'invalid gender',
            'mobile_phone'        => 'invalid phone',
            'year_of_birth'       => 'invalid year',
            'appointment'         => 'invalid appointment',
            'serving_as'          => 'invalid serving as',
            'marital_status'      => 'invalid marital status',
            'responsible_brother' => 'invalid responsible brother',
            'is_unrestricted'     => 'invalid unrestricted',
        ];

        Mail::fake();
        $this->actingAs($admin)
            ->postJson("/admin/users/", $userData)
            ->assertUnprocessable()
            ->assertInvalid([
                'name',
                'email',
                'role',
                'gender',
                'mobile_phone',
                'year_of_birth',
                'appointment',
                'serving_as',
                'marital_status',
                'responsible_brother',
                'is_unrestricted',
            ]);
        $this->assertDatabaseCount('users', 1);
        Mail::assertNothingSent();
    }

    public function test_admin_can_edit_user_and_test_bad_email(): void
    {
        $admin    = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $oldEmail = 'foo@example.com';
        $user     = User::factory()->enabled()->state(['email' => $oldEmail])->create();
        $this->assertEquals($oldEmail, $user->fresh()->email);

        $userData          = $user->toArray();
        $newEmail          = 'invalid email';
        $userData['email'] = $newEmail;

        $this->actingAs($admin)
            ->putJson("/admin/users/{$user->getKey()}", $userData)
            ->assertUnprocessable();

        $newEmail          = 'bar@example.com';
        $userData['email'] = $newEmail;

        $this->actingAs($admin)
            ->putJson("/admin/users/{$user->getKey()}", $userData)
            ->assertRedirect("/admin/users/{$user->getKey()}/edit")
            ->assertSessionHas('flash.banner', "User $user->name successfully modified.");

        $this->assertEquals($newEmail, $user->fresh()->email);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->create();
        $this->assertDatabaseCount('users', 2);

        $this->actingAs($admin)
            ->deleteJson("/admin/users/{$user->getKey()}")
            ->assertRedirect()
            ->assertSessionHas('flash.banner', "User $user->name successfully deleted.");
        $this->assertDatabaseCount('users', 1);

        $this->actingAs($admin)
            ->deleteJson("/admin/users/9999999")
            ->assertnotFound();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_can_send_password_reset_email(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->create();

        // Note: password reset uses the 'notifications' feature of Laravel, not the 'mail' feature
        Mail::fake();
        Notification::fake();
        $this->actingAs($admin)
            ->postJson("/admin/resend-welcome-email?user_id={$user->getKey()}")
            ->assertOk()
            ->assertJsonPath('message', 'A password reset link has been sent to the user.');
        Notification::assertSentToTimes($user, ResetPassword::class);
        Mail::assertNothingSent();
    }

    public function test_admin_can_resend_welcome_email(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->state(['password' => null])->create();

        Mail::fake();
        $this->actingAs($admin)
            ->postJson("/admin/resend-welcome-email?user_id={$user->getKey()}")
            ->assertOk()
            ->assertJsonPath('message', 'Welcome email was sent');

        Mail::assertSent(UserAccountCreated::class, 1);
    }

    public function test_welcome_email_is_correct(): void
    {
        $user = User::factory()->enabled()->state(['password' => null])->create();

        $textMatch = "Dear $user->name, an account has been created for you on the " . config('app.name') . " Public Witnessing web application.";
        $mailable  = (new UserAccountCreated($user))
            ->assertHasSubject(config('app.name') . ' Account Activation')
            ->assertSeeInHtml($textMatch)
            ->assertSeeInText($textMatch);


        $render = $mailable->render();
        $hashed = Str::of($render)->match('/set-password\/\d+\/([a-zA-Z0-9]+)/');
        $this->assertTrue(Hash::check($user->uuid . $user->email, base64_decode($hashed)));
    }

    public function test_password_reset_email_is_correct(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->state(['password' => null])->create();

        Mail::fake();
        $this->actingAs($admin)
            ->postJson("/admin/resend-welcome-email?user_id={$user->getKey()}")
            ->assertOk()
            ->assertJsonPath('message', 'Welcome email was sent');

        Mail::assertSent(UserAccountCreated::class, 1);
    }


    public function test_validations_are_working(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();

        $userData = [
            'name'                => str_repeat('a', 256),
            'email'               => $admin->email,
            'role'                => 'peanut',
            'gender'              => 'gorilla',
            'mobile_phone'        => 'my phone number',
            'year_of_birth'       => '1900',
            'appointment'         => 'peanut',
            'serving_as'          => 'hyena',
            'marital_status'      => 'watermelon',
            'responsible_brother' => 'yes',
            'is_unrestricted'     => 'no',
        ];

        $this->actingAs($admin)
            ->postJson("/admin/users/", $userData)
            ->assertUnprocessable()
            ->assertInvalid(array_keys($userData));

        $this->assertDatabaseCount('users', 1);
    }

    public function test_duplicate_user_is_not_created(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->create();
        $user2 = User::factory()->enabled()->state(['email' => $user->email])->makeOne();


        $this->actingAs($admin)
            ->postJson("/admin/users/", $user2->toArray())
            ->assertUnprocessable()
            ->assertInvalid(['email']);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_phone_number_is_created_properly(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->makeOne();

        $userData                 = $user->toArray();
        $userData['mobile_phone'] = '1 234 4567 89 ';

        $this->actingAs($admin)
            ->postJson("/admin/users/", $userData)
            ->assertRedirect();

        $this->assertDatabaseCount('users', 2);

        $user = User::firstWhere('name', $userData['name']);
        $this->assertEquals('1234 456 789', $user->mobile_phone);

        $storedDbValue = DB::table('users')->where('id', $user->id)->value('mobile_phone');
        $this->assertEquals('1234456789', $storedDbValue);
    }

    public function test_phone_number_is_updated_properly(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->state(['mobile_phone' => '1111111111'])->create();

        $userData                 = $user->toArray();
        $userData['mobile_phone'] = '1 234 4567 89 ';

        $this->actingAs($admin)
            ->putJson("/admin/users/{$user->getKey()}", $userData)
            ->assertRedirect("/admin/users/{$user->getKey()}/edit");

        $this->assertEquals('1234 456 789', $user->fresh()->mobile_phone);

        $userData['mobile_phone'] = '+61412345678';

        $this->actingAs($admin)
            ->putJson("/admin/users/{$user->getKey()}", $userData)
            ->assertRedirect("/admin/users/{$user->getKey()}/edit")
            ->assertSessionHas('flash.banner', "User $user->name successfully modified.");

        $storedDbValue = DB::table('users')->where('id', $user->id)->value('mobile_phone');
        $this->assertEquals('0412345678', $storedDbValue);

        $this->assertEquals('0412 345 678', $user->fresh()->mobile_phone);
    }

    public function test_admin_can_add_update_delete_user_vacations(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->create();

        $vacationData = [
            'user_id'   => $user->getKey(),
            'vacations' => [
                ['start_date' => '2023-01-01', 'end_date' => '2023-01-15', 'description' => 'Testing'],
                ['start_date' => '2023-02-01', 'end_date' => '2023-02-15', 'description' => 'Testing 2'],
            ],
        ];

        $this->actingAs($admin)
            ->putJson("/user/vacations", $vacationData)
            ->assertRedirect("/admin/users/{$user->getKey()}/edit");

        $user->refresh()->load(['vacations']);
        $this->assertCount(2, $user->vacations);
        $this->assertSame('Testing', $user->vacations[0]->description);
        $this->assertSame('Testing 2', $user->vacations[1]->description);
        $vacation              = $user->vacations[0];
        $vacation->start_date  = '2023-01-07';
        $vacation->description = 'Testing Updated';
        $this->actingAs($admin)
            ->putJson("/user/vacations", [
                'user_id'   => $user->getKey(),
                'vacations' => [$vacation->toArray()],
            ])
            ->assertRedirect("/admin/users/{$user->getKey()}/edit");

        $user->refresh()->load(['vacations']);
        $this->assertCount(2, $user->vacations);
        $this->assertSame('2023-01-07', $user->vacations[0]->start_date);
        $this->assertSame('Testing Updated', $user->vacations[0]->description);
        $this->assertSame('Testing 2', $user->vacations[1]->description);

        $this->actingAs($admin)
            ->putJson("/user/vacations", [
                'user_id'          => $user->getKey(),
                'deletedVacations' => [['id' => $vacation->getKey()]],
            ])
            ->assertRedirect("/admin/users/{$user->getKey()}/edit");

        $user->refresh()->load(['vacations']);
        $this->assertCount(1, $user->vacations);
        $this->assertSame('Testing 2', $user->vacations[0]->description);
    }

    public function test_admin_can_maintain_user_location_choices(): void
    {
        $settings                            = app()->make(GeneralSettings::class);
        $settings->enableUserLocationChoices = true;
        $settings->save();

        $admin     = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user      = User::factory()->enabled()->create();
        $locations = Location::factory()->count(3)->create();

        $choiceData = [
            'user_id'           => $user->getKey(),
            'selectedLocations' => [
                $locations[0]->getKey(),
                $locations[2]->getKey(),
            ],
        ];

        $this->actingAs($admin)
            ->putJson("/user/available-locations", $choiceData)
            ->assertRedirect("/admin/users/{$user->getKey()}/edit")
            ->assertSessionHas('flash.banner', "volunteer preferred locations have been updated.");

        $user->refresh()->load(['rosterLocations']);
        $this->assertCount(2, $user->rosterLocations);
        $this->assertSame($locations[0]->name, $user->rosterLocations[0]->name);
        $this->assertSame($locations[2]->name, $user->rosterLocations[1]->name);
        $this->assertNotSame($locations[1]->name, $user->rosterLocations[0]->name, 'Verify data is not duplicated');

        $choiceData['selectedLocations'][1] = $locations[1]->getKey();

        $this->actingAs($admin)
            ->putJson("/user/available-locations", $choiceData)
            ->assertRedirect("/admin/users/{$user->getKey()}/edit");

        $user->refresh()->load(['rosterLocations']);
        $this->assertSame($locations[0]->name, $user->rosterLocations[0]->name);
        $this->assertSame($locations[1]->name, $user->rosterLocations[1]->name);
    }

    public function test_admin_cant_maintain_disabled_feature_of_user_location_choices(): void
    {
        $admin     = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user      = User::factory()->enabled()->create();
        $locations = Location::factory()->count(3)->create();

        $choiceData = [
            'user_id'           => $user->getKey(),
            'selectedLocations' => [
                $locations[0]->getKey(),
                $locations[2]->getKey(),
            ],
        ];

        $this->actingAs($admin)
            ->putJson("/user/available-locations", $choiceData)
            ->assertInvalid(['featureDisabled']);
    }
}
