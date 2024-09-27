<?php

namespace Tests\Feature\App\Admin;

use App\Enums\Role;
use App\Mail\UserAccountCreated;
use App\Models\Location;
use App\Models\User;
use App\Models\UserAvailability;
use App\Models\UserVacation;
use App\Settings\GeneralSettings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_retrieve_all_users(): void
    {
        $admin = User::factory()->enabled()->adminRoleUser()->create();
        User::factory()->enabled()->count(5)->create();

        $this->actingAs($admin)
            ->get("/admin/users")
            ->assertOk()
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Admin/Users/List')
                ->has('users.data', 6)
            );
    }

    public function test_admin_can_show_create_user_form(): void
    {
        $admin = User::factory()->enabled()->adminRoleUser()->create();

        $this->actingAs($admin)
            ->getJson("/admin/users/create")
            ->assertOk()
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Admin/Users/Add')
            );
    }

    public function test_admin_can_show_edit_user_form(): void
    {
        $admin = User::factory()->enabled()->adminRoleUser()->create();

        $mondayHours = range(8, 12);

        $wife    = User::factory()->enabled()->female()->create();
        $husband = User::factory()
            ->enabled()
            ->male()
            ->state(['spouse_id' => $wife->id])
            ->has(
                UserAvailability::factory()
                    ->state([
                        'day_monday'  => $mondayHours,
                        'num_mondays' => 3,
                    ])
                , 'availability')
            ->has(UserVacation::factory(), 'vacations')
            ->has(Location::factory()->count(2), 'rosterLocations')
            ->create();

        $this->actingAs($admin)
            ->get("/admin/users/$husband->id/edit")
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Admin/Users/Edit')
                ->has('editUser.data', fn(AssertableInertia $data) => $data
                    ->where('id', $husband->id)
                    ->where('spouse_name', $husband->spouse->name)
                    ->where('spouse_id', $husband->spouse->id)
                    ->where('selectedLocations.0', $husband->rosterLocations[0]->id)
                    ->where('vacations.0.id', $husband->vacations[0]->id)
                    ->where('availability.user_id', $husband->id)
                    ->where('availability.day_monday', $mondayHours)
                    ->where('availability.comments', $husband->availability->comments)
                    ->etc()
                )
            );
    }

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

    public function test_restrict_admin_user_should_remove_admin_rights(): void
    {
        $admin     = User::factory()->enabled()->adminRoleUser()->create();
        $adminUser = User::factory()->enabled()->adminRoleUser()->create();

        GeneralSettings::fake(['allowedSettingsUsers' => [$admin->id, $adminUser->id]]);

        $this->actingAs($admin)
            ->putJson("/admin/users/$adminUser->id", [
                'id'                  => $adminUser->id,
                'name'                => $adminUser->name,
                'email'               => $adminUser->email,
                'mobile_phone'        => $adminUser->mobile_phone,
                'role'                => Role::Admin->value,
                'gender'              => $adminUser->gender,
                'year_of_birth'       => $adminUser->year_of_birth,
                'appointment'         => $adminUser->appointment,
                'serving_as'          => $adminUser->serving_as,
                'marital_status'      => $adminUser->marital_status,
                'responsible_brother' => $adminUser->responsible_brother,
                'is_unrestricted'     => false,
            ])
            ->assertRedirect("/admin/users/$adminUser->id/edit");

        $this->assertDatabaseHas('users', ['id' => $adminUser->id, 'role' => Role::User->value, 'is_unrestricted' => false]);
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

    public function test_password_reset_too_many_requests(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->create();

        Mail::fake();
        Notification::fake();
        Password::shouldReceive('sendResetLink')
            ->andReturn(Password::RESET_LINK_SENT, Password::RESET_LINK_SENT, Password::RESET_THROTTLED);

        for ($i = 0; $i < 3; $i++) {
            // 3rd request should fail
            $expected = $i < 2 ? 'has been sent' : 'too many password reset attempts';
            $this->actingAs($admin)
                ->postJson("/admin/resend-welcome-email?user_id={$user->getKey()}")
                ->assertStatus($i < 2 ? 200 : 429)
                ->assertContainsStringIgnoringCase('message', $expected);
        }
        Mail::assertNothingSent();
    }

    public function test_password_reset_unhandled_password_response(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user  = User::factory()->enabled()->create();

        Mail::fake();
        Notification::fake();
        Password::shouldReceive('sendResetLink')
            ->andReturn(Password::INVALID_USER);

        // 3rd request should fail
        $this->actingAs($admin)
            ->postJson("/admin/resend-welcome-email?user_id={$user->getKey()}")
            ->assertserverError()
            ->assertContainsStringIgnoringCase('message', 'unknown error');
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

    public function test_resend_welcome_email_is_correct(): void
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

    public function test_admin_can_add_update_user_regular_availability(): void
    {
        $settings                         = app()->make(GeneralSettings::class);
        $settings->enableUserAvailability = true;
        $settings->save();

        $admin        = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user         = User::factory()->enabled()->create();
        $availability = UserAvailability::factory()->wedThuTenToOne()->state(['user_id' => $user->getKey()])->makeOne();

        $this->assertDatabaseEmpty('user_availabilities');
        $this->actingAs($admin)
            ->putJson("/user/availability", $availability->toArray())
            ->assertRedirect("/admin/users/{$user->getKey()}/edit");
        $this->assertDatabaseCount('user_availabilities', 1);
        $this->assertDatabaseHas('user_availabilities', Arr::except($availability->getAttributes(), ['created_at', 'updated_at']));

        $availability->num_wednesdays = 0;
        $availability->num_fridays    = 1;
        $availability->day_wednesday  = null;
        $availability->day_friday     = range(10, 13);
        $availability->comments       = "Testing";

        $this->actingAs($admin)
            ->putJson("/user/availability", $availability->toArray())
            ->assertRedirect("/admin/users/{$user->getKey()}/edit");
        $this->assertDatabaseHas('user_availabilities', Arr::except($availability->getAttributes(), ['created_at', 'updated_at']));
    }

    public function test_admin_can_maintain_user_location_choices(): void
    {
        $settings                            = app()->make(GeneralSettings::class);
        $settings->enableUserLocationChoices = true;
        $settings->save();

        $admin     = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        $user      = User::factory()->enabled()->create();
        $locations = Location::factory()->count(6)->create();

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

        $rosterLocations = $user->rosterLocations->pluck('name');
        $this->assertContains($locations[0]->name, $rosterLocations);
        $this->assertContains($locations[2]->name, $rosterLocations);
        $this->assertNotContains($locations[1]->name, $rosterLocations, 'Verify data is not duplicated');

        $choiceData['selectedLocations'][1] = $locations[1]->getKey();

        $this->actingAs($admin)
            ->putJson("/user/available-locations", $choiceData)
            ->assertRedirect("/admin/users/{$user->getKey()}/edit");

        $user->refresh()->load(['rosterLocations']);
        $rosterLocations = $user->rosterLocations->pluck('name');
        $this->assertContains($locations[0]->name, $rosterLocations);
        $this->assertContains($locations[1]->name, $rosterLocations);
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

    public function test_get_admin_users_only_returns_users_with_admin_role(): void
    {
        $admins = User::factory()->count(3)->adminRoleUser()->create();
        User::factory()->enabled()->count(3)->create();

        $this->actingAs($admins[0])
            ->getJson("/admin/admin-users")
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $admins[0]->id, 'name' => $admins[0]->name],
                    ['id' => $admins[1]->id, 'name' => $admins[1]->name],
                    ['id' => $admins[2]->id, 'name' => $admins[2]->name],
                ],
            ]);
    }
}
