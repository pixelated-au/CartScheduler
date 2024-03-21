<?php

namespace Tests\Feature\App\Admin;

use App\Actions\ErrorApiResource;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_get_a_list_of_users(): void
    {
        User::factory()->userRoleUser()->count(3)->create(['is_enabled' => true]);
        $nonAdminUser = User::factory()->userRoleUser()->create(['is_enabled' => true]);

        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $date = '2023-01-03'; // A Tuesday

        $shiftId  = $location->shifts[0]->id;
        $response = $this->actingAs($nonAdminUser)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);

        $response->assertForbidden();
    }

    public function test_admin_can_get_a_list_of_users(): void
    {
        $admin      = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $users      = User::factory()->userRoleUser()->count(3)->create(['is_enabled' => true]);
        $firstUser  = $users->first();
        $secondUser = $users->get(1);

        $location = Location::factory()->create([
            'requires_brother' => true,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        $date  = '2023-01-03'; // A Tuesday

        $shiftId  = $shift->id;
        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);
        // 4 users in the system. Should have 'available' 4 users returned
        $response->assertJsonCount(4, 'data');
        $response->assertJsonPath('data.1.id', $firstUser->getKey());

        // Assign the first user to a shift
        ShiftUser::factory()->create([
            'shift_id'   => $shiftId,
            'user_id'    => $firstUser->getKey(),
            'shift_date' => $date,
        ]);
        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);
        // Should now only have 'available' 3 users returned
        $response->assertJsonCount(3, 'data');
        $response->assertJsonMissing(['data.*.id' => $firstUser->getKey()]);

        //Now test that the returned data doesn't include users who are already assigned to another shift at the same time
        $location = Location::factory()->create([
            'requires_brother' => true,
        ]);
        /** @var Shift $shift2 */
        $shift2 = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        ShiftUser::factory()->create([
            'shift_id'   => $shift2->id,
            'user_id'    => $secondUser->getKey(),
            'shift_date' => $date,
        ]);
        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shift2->id", ['date' => $date]);
        // Should now only have 'available' 3 users returned
        $response->assertJsonCount(2, 'data');
        $response->assertJsonMissing(['data.*.id' => $firstUser->getKey()]);
        $response->assertJsonMissing(['data.*.id' => $secondUser->getKey()]);
    }

    /** Make sure that when a shift is made up of sisters, the last spot returns brothers only. */
    public function test_admin_receives_male_volunteers_when_last_spot_requires_brother_shift(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        User::factory()->male()->count(3)->create(['is_enabled' => true]);
        $sisters = User::factory()->female()->count(3)->create(['is_enabled' => true]);

        $this->assertDatabaseCount('users', 7);

        $location = Location::factory()->create([
            'max_volunteers'   => 3,
            'requires_brother' => true,
        ]);

        /** @var Shift $shift */
        $shift   = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        $shiftId = $shift->id;

        $date = '2023-01-03'; // A Tuesday
        // Attach a sister to the shift
        ShiftUser::factory()->create([
            'shift_id'   => $shiftId,
            'user_id'    => $sisters->get(0)->getKey(),
            'shift_date' => $date,
        ]);

        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);
        // 7 users in the system, 1 female assigned. Should have 6 users returned
        $response->assertJsonCount(6, 'data');

        // Attach another sister so now no more sisters should be returned on the next request
        ShiftUser::factory()->create([
            'shift_id'   => $shift->id,
            'user_id'    => $sisters->get(1)->getKey(),
            'shift_date' => $date,
        ]);

        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);
        // Should only have male users returned
        $response->assertJsonCount(4, 'data');
        $response->assertJsonPath('data.0.gender', 'male');
        $response->assertJsonPath('data.1.gender', 'male');
        $response->assertJsonPath('data.2.gender', 'male');
        $response->assertJsonPath('data.3.gender', 'male');
    }

    public function test_list_of_users_doesnt_include_disabled(): void
    {
        $admin       = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $enabledUser = User::factory()->userRoleUser()->count(4)->create(['is_enabled' => true])->get(0);
        User::factory()->userRoleUser()->count(5)->create(['is_enabled' => false]);

        $this->assertDatabaseCount('users', 10);

        $location = Location::factory()->create([
            'requires_brother' => true,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        $date  = '2023-01-03'; // A Tuesday

        $shiftId  = $shift->id;
        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);
        // 10 users in the system but should only have retrieve 5 active users because 5 are disabled
        $response->assertJsonCount(5, 'data');
        $response->assertJsonPath('data.0.id', $admin->getKey());
        $response->assertJsonPath('data.1.id', $enabledUser->getKey());
    }

    public function test_admin_can_assign_a_user_to_a_shift(): void
    {
        $admin        = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $enabledUser  = User::factory()->userRoleUser()->count(4)->create(['is_enabled' => true])->get(0);
        $disabledUser = User::factory()->userRoleUser()->count(5)->create(['is_enabled' => false])->get(0);

        $this->assertDatabaseCount('users', 10);

        $location = Location::factory()->create([
            'requires_brother' => true,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        $this->travelTo('2023-01-02 09:00:00');
        $date = '2023-01-03'; // A Tuesday

        $this->assertDatabaseCount('shift_user', 0);

        $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date'       => $date,
                    'do_reserve' => true,
                    'location'   => $location->id,
                    'shift'      => $shift->id,
                    'user'       => $enabledUser->getKey(),
                ]
            )
            ->assertOk();

        $this->assertDatabaseCount('shift_user', 1);
        $this->assertDatabaseHas('shift_user', [
            'shift_id'   => $shift->id,
            'user_id'    => $enabledUser->getKey(),
            'shift_date' => $date,
        ]);

        $this->actingAs($admin)
            ->deleteJson("/admin/toggle-shift-for-user", [
                    'date'       => $date,
                    'do_reserve' => false,
                    'location'   => $location->id,
                    'shift'      => $shift->id,
                    'user'       => $enabledUser->getKey(),
                ]
            )->assertOk();

        $this->assertDatabaseCount('shift_user', 0);
        $this->assertDatabaseMissing('shift_user', [
            'shift_id'   => $shift->id,
            'user_id'    => $enabledUser->getKey(),
            'shift_date' => $date,
        ]);

        // Test that adding an 'inactive' user fails
        $response = $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date'       => $date,
                    'do_reserve' => true,
                    'location'   => $location->id,
                    'shift'      => $shift->id,
                    'user'       => $disabledUser->getKey(),
                ]
            );
        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('has been disabled', $response->json('message'));
    }

    public function test_gender_restrictions_are_enforced_on_shift_requiring_a_brother(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $users = User::factory()->female()->count(3)->create();

        $this->assertDatabaseCount('users', 4);

        $location = Location::factory()->create([
            'requires_brother' => true,
            'min_volunteers'   => 3,
            'max_volunteers'   => 3,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        $this->travelTo('2023-01-02 09:00:00');
        $date = '2023-01-03'; // A Tuesday

        $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date'       => $date,
                    'do_reserve' => true,
                    'location'   => $location->id,
                    'shift'      => $shift->id,
                    'user'       => $users->get(0)->getKey(),
                ]
            )->assertOk();
        $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date'       => $date,
                    'do_reserve' => true,
                    'location'   => $location->id,
                    'shift'      => $shift->id,
                    'user'       => $users->get(1)->getKey(),
                ]
            )->assertOk();

        $response = $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date'       => $date,
                    'do_reserve' => true,
                    'location'   => $location->id,
                    'shift'      => $shift->id,
                    'user'       => $users->get(2)->getKey(),
                ]
            );
        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase("the last volunteer for this shift needs to be a brother", $response->json('message'));
    }

    public function test_volunteer_can_be_moved_to_a_different_shift(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        $locations = Location::factory()
            ->state(['max_volunteers' => 3])
            ->count(2)
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(User::factory()
                    ->userRoleUser()
                    ->count(3)
                    ->state(['is_enabled' => true])
                    , ['shift_date' => '2023-01-03']
                ))
            ->create();

        $shifts = $locations->map->shifts->flatten();

        // Just to be sure we have the correct number of shifts
        $this->assertCount(2, $shifts);
        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(3, $shifts[1]->users);

        // Remove a user from second shift - to enable a user to move into the spot
        $shifts[1]->users()->detach($shifts[1]->users->last());
        $shifts[1]->refresh();
        $this->assertCount(2, $shifts[1]->users);

        $date = '2023-01-03'; // A Tuesday

        $response = $this->actingAs($admin)
            ->putJson("/admin/move-volunteer-to-shift", [
                'date'         => $date,
                'location_id'  => $shifts[1]->location->id,
                'old_shift_id' => $shifts[0]->getKey(),
                'user_id'      => $shifts[0]->users->last()->getKey(),
            ]);
        $response->assertSuccessful();
        $shifts->each->refresh();

        $this->assertCount(2, $shifts[0]->users);
        $this->assertCount(3, $shifts[1]->users);
    }

    public function test_volunteer_cannot_be_moved_to_a_full_shift(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        $locations = Location::factory()
            ->state(['max_volunteers' => 3, 'requires_brother' => false])
            ->count(2)
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(User::factory()
                    ->userRoleUser()
                    ->count(3)
                    ->state(['is_enabled' => true])
                    , ['shift_date' => '2023-01-03']
                ))
            ->create();

        $shifts = $locations->map->shifts->flatten();

        // Just to be sure we have the correct number of shifts
        $this->assertCount(2, $shifts);
        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(3, $shifts[1]->users);

        // Remove a user from second shift - to enable a user to move into the spot
        $date = '2023-01-03'; // A Tuesday

        $response = $this->actingAs($admin)
            ->putJson("/admin/move-volunteer-to-shift", [
                'date'         => $date,
                'location_id'  => $shifts[1]->location->id, // This is the location of shift volunteer will be moved to
                'old_shift_id' => $shifts[0]->getKey(),
                'user_id'      => $shifts[0]->users->last()->getKey(),
            ]);
        $response->assertStatus(422);
        $response->assertJsonPath('error_code', ErrorApiResource::CODE_SHIFT_AT_MAX_CAPACITY);

        $shifts->each->refresh();
        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(3, $shifts[1]->users);
    }

    public function test_sister_cannot_be_moved_to_a_shift_requiring_a_brother(): void
    {
        $admin     = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $locations = Location::factory()
            ->count(2)
            ->state([
                'max_volunteers'   => 3,
                'requires_brother' => true,
            ])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(User::factory()
                    ->userRoleUser()
                    ->count(3)
                    ->state([
                        'is_enabled' => true,
                        'gender'     => 'female'
                    ])
                    , ['shift_date' => '2023-01-03']
                )
            )
            ->create();

        $shifts = $locations->map->shifts->flatten();

        // Just to be sure we have the correct number of shifts
        $this->assertCount(2, $shifts);
        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(3, $shifts[1]->users);

        // Remove a user from second shift - to enable a user to move into the spot
        $shifts[1]->users()->detach($shifts[1]->users->last());
        $shifts[1]->refresh();
        $this->assertCount(2, $shifts[1]->users);

        $date = '2023-01-03'; // A Tuesday

        $response = $this->actingAs($admin)
            ->putJson("/admin/move-volunteer-to-shift", [
                'date'         => $date,
                'location_id'  => $shifts[1]->location->id,
                'old_shift_id' => $shifts[0]->getKey(),
                'user_id'      => $shifts[0]->users->last()->getKey(),
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error_code', ErrorApiResource::CODE_BROTHER_REQUIRED);
        $shifts->each->refresh();

        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(2, $shifts[1]->users);
    }


    public function test_move_volunteer_when_duplicate_shifts_with_one_disabled(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        /** @var Collection<Location> $locations */
        $locations   = collect();
        $locations[] = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->state(['is_enabled' => true])
                ->hasAttached(User::factory()
                    ->userRoleUser()
                    ->count(3)
                    ->state(['is_enabled' => true])
                    , ['shift_date' => '2023-01-03']
                ))
            ->create();

        $locations[] = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->count(2)
                ->everyDay9am()
                ->sequence(['is_enabled' => true], ['is_enabled' => false])
                ->hasAttached(User::factory()
                    ->userRoleUser()
                    ->count(2)
                    ->state(['is_enabled' => true])
                    , ['shift_date' => '2023-01-03']
                ))
            ->create();

        $locations->each->load(['shifts', 'shifts.users', 'shifts.location']);
        $shifts = $locations->map->shifts->flatten();

        // Just to be sure we have the correct number of shifts
        $this->assertCount(3, $shifts);
        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(2, $shifts[1]->users);
        $this->assertCount(2, $shifts[2]->users);

        $this->assertTrue($shifts[0]->is_enabled);
        $this->assertTrue($shifts[1]->is_enabled);
        // Make sure the disabled shift is not enabled
        $this->assertFalse($shifts[2]->is_enabled);

        // Remove a user from second shift - to enable a user to move into the spot
        $shifts[1]->users()->detach($shifts[0]->users->last());
        $shifts[1]->refresh();
        $this->assertCount(2, $shifts[1]->users);

        $date = '2023-01-03'; // A Tuesday

        $movingUserId = $shifts[0]->users->last()->getKey();
        $response     = $this->actingAs($admin)
            ->putJson("/admin/move-volunteer-to-shift", [
                'date'         => $date,
                'location_id'  => $shifts[1]->location->id,
                'old_shift_id' => $shifts[0]->getKey(),
                'user_id'      => $movingUserId,
            ]);
        $response->assertSuccessful();
        $shifts->each->refresh();

        $this->assertCount(2, $shifts[0]->users);
        $this->assertCount(3, $shifts[1]->users);
        $this->assertCount(2, $shifts[2]->users);

        // Now move the volunteer back to the first shift, disable shift 2 and enable shift 3. Then try again.
        $response = $this->actingAs($admin)
            ->putJson("/admin/move-volunteer-to-shift", [
                'date'         => $date,
                'location_id'  => $shifts[0]->location->id,
                'old_shift_id' => $shifts[1]->getKey(),
                'user_id'      => $movingUserId,
            ]);
        $response->assertSuccessful();
        $shifts->each->refresh();

        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(2, $shifts[1]->users);
        $this->assertCount(2, $shifts[2]->users);

        $shifts[1]->is_enabled = false;
        $shifts[1]->save();

        $shifts[2]->is_enabled = true;
        $shifts[2]->save();

        // Now, check that the volunteer will move to the third shift.
        $response = $this->actingAs($admin)
            ->putJson("/admin/move-volunteer-to-shift", [
                'date'         => $date,
                'location_id'  => $shifts[1]->location->id,
                'old_shift_id' => $shifts[0]->getKey(),
                'user_id'      => $movingUserId,
            ]);

        $response->assertSuccessful();
        $shifts->each->refresh();

        $this->assertCount(2, $shifts[0]->users);
        $this->assertCount(2, $shifts[1]->users);
        $this->assertCount(3, $shifts[2]->users);
    }

    public function test_fail_move_volunteer_when_only_shift_is_disabled(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        /** @var Collection<Location> $locations */
        $locations   = collect();
        $locations[] = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->state(['is_enabled' => true])
                ->hasAttached(User::factory()
                    ->userRoleUser()
                    ->count(3)
                    ->state(['is_enabled' => true])
                    , ['shift_date' => '2023-01-03']
                ))
            ->create();

        $locations[] = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->state(['is_enabled' => false])
                ->hasAttached(User::factory()
                    ->userRoleUser()
                    ->count(2)
                    ->state(['is_enabled' => true])
                    , ['shift_date' => '2023-01-03']
                ))
            ->create();

        $locations->each->load(['shifts', 'shifts.users', 'shifts.location']);
        $shifts = $locations->map->shifts->flatten();

        // Just to be sure we have the correct number of shifts
        $this->assertCount(2, $shifts);
        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(2, $shifts[1]->users);

        $this->assertTrue($shifts[0]->is_enabled);
        // Make sure the disabled shift is not enabled
        $this->assertFalse($shifts[1]->is_enabled);

        $date = '2023-01-03'; // A Tuesday

        $response = $this->actingAs($admin)
            ->putJson("/admin/move-volunteer-to-shift", [
                'date'         => $date,
                'location_id'  => $shifts[1]->location->id,
                'old_shift_id' => $shifts[0]->getKey(),
                'user_id'      => $shifts[0]->users->last()->getKey(),
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error_code', ErrorApiResource::CODE_SHIFT_NOT_FOUND);
        $shifts->each->refresh();

        $this->assertCount(3, $shifts[0]->users);
        $this->assertCount(2, $shifts[1]->users);
    }
}
