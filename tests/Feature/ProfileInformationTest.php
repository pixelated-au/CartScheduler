<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->put('/user/profile-information', [
            'name'  => 'Test Name',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }

    public function test_user_can_add_update_delete_vacations(): void
    {
        $user  = User::factory()->enabled()->create();

        $vacationData = [
            'vacations' => [
                ['start_date' => '2023-01-01', 'end_date' => '2023-01-15', 'description' => 'Testing'],
                ['start_date' => '2023-02-01', 'end_date' => '2023-02-15', 'description' => 'Testing 2'],
            ],
        ];

        $this->actingAs($user)
            ->putJson("/user/vacations", $vacationData)
            ->assertRedirect("/user/availability");

        $user->refresh()->load(['vacations']);
        $this->assertCount(2, $user->vacations);
        $this->assertSame('Testing', $user->vacations[0]->description);
        $this->assertSame('Testing 2', $user->vacations[1]->description);
        $vacation              = $user->vacations[0];
        $vacation->start_date  = '2023-01-07';
        $vacation->description = 'Testing Updated';
        $this->actingAs($user)
            ->putJson("/user/vacations", [
                'vacations' => [$vacation->toArray()],
            ])
            ->assertRedirect("/user/availability");

        $user->refresh()->load(['vacations']);
        $this->assertCount(2, $user->vacations);
        $this->assertSame('2023-01-07', $user->vacations[0]->start_date);
        $this->assertSame('Testing Updated', $user->vacations[0]->description);
        $this->assertSame('Testing 2', $user->vacations[1]->description);

        $this->actingAs($user)
            ->putJson("/user/vacations", [
                'deletedVacations' => [['id' => $vacation->getKey()]],
            ])
            ->assertRedirect("/user/availability");

        $user->refresh()->load(['vacations']);
        $this->assertCount(1, $user->vacations);
        $this->assertSame('Testing 2', $user->vacations[0]->description);
    }

    public function test_user_can_maintain_location_choices(): void
    {
        $settings                            = app()->make(GeneralSettings::class);
        $settings->enableUserLocationChoices = true;
        $settings->save();

        $user      = User::factory()->enabled()->create();
        $locations = Location::factory()->count(3)->create();

        $choiceData = [
            'selectedLocations' => [
                $locations[0]->getKey(),
                $locations[2]->getKey(),
            ],
        ];

        $this->actingAs($user)
            ->putJson("/user/available-locations", $choiceData)
            ->assertRedirect("/user/availability")
            ->assertSessionHas('flash.banner', "your preferred locations have been updated.");

        $user->refresh()->load(['rosterLocations']);
        $this->assertCount(2, $user->rosterLocations);
        $this->assertSame($locations[0]->name, $user->rosterLocations[0]->name);
        $this->assertSame($locations[2]->name, $user->rosterLocations[1]->name);
        $this->assertNotSame($locations[1]->name, $user->rosterLocations[0]->name, 'Verify data is not duplicated');

        $choiceData['selectedLocations'][1] = $locations[1]->getKey();

        $this->actingAs($user)
            ->putJson("/user/available-locations", $choiceData)
            ->assertRedirect("/user/availability");

        $user->refresh()->load(['rosterLocations']);
        $this->assertSame($locations[0]->name, $user->rosterLocations[0]->name);
        $this->assertSame($locations[1]->name, $user->rosterLocations[1]->name);
    }

    public function test_user_cant_maintain_disabled_feature_of_user_location_choices(): void
    {
        $user      = User::factory()->enabled()->create();
        $locations = Location::factory()->count(3)->create();

        $choiceData = [
            'selectedLocations' => [
                $locations[0]->getKey(),
                $locations[2]->getKey(),
            ],
        ];

        $this->actingAs($user)
            ->putJson("/user/available-locations", $choiceData)
            ->assertInvalid(['featureDisabled']);
    }

    public function test_non_admin_cannot_update_another_users_location_choices_or_vacations(): void
    {
        $settings                            = app()->make(GeneralSettings::class);
        $settings->enableUserLocationChoices = true;
        $settings->save();

        $user      = User::factory()->enabled()->create();
        $user2     = User::factory()->enabled()->create();
        $locations = Location::factory()->count(3)->create();

        $choiceData = [
            'user_id'           => $user2->getKey(),
            'selectedLocations' => [
                $locations[0]->getKey(),
                $locations[2]->getKey(),
            ],
        ];

        $this->actingAs($user)
            ->putJson("/user/available-locations", $choiceData)
            ->assertUnauthorized();

        $vacationData = [
            'user_id'   => $user2->getKey(),
            'vacations' => [
                ['start_date' => '2023-01-01', 'end_date' => '2023-01-15', 'description' => 'Testing'],
                ['start_date' => '2023-02-01', 'end_date' => '2023-02-15', 'description' => 'Testing 2'],
            ],
        ];

        $this->actingAs($user)
            ->putJson("/user/vacations", $vacationData)
            ->assertUnauthorized();
    }

}
