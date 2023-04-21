<?php

namespace Tests\Feature\App\Admin;

use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserReservationsTest extends TestCase
{
    use RefreshDatabase;

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
            'shift_id' => $shiftId,
            'user_id' => $firstUser->getKey(),
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
            'shift_id' => $shift2->id,
            'user_id' => $secondUser->getKey(),
            'shift_date' => $date,
        ]);
        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shift2->id", ['date' => $date]);
        // Should now only have 'available' 3 users returned
        $response->assertJsonCount(2, 'data');
        $response->assertJsonMissing(['data.*.id' => $firstUser->getKey()]);
        $response->assertJsonMissing(['data.*.id' => $secondUser->getKey()]);
    }

    public function test_admin_receives_gender_relevant_users(): void
    {
        $admin      = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        User::factory()->male()->count(3)->create(['is_enabled' => true]);
        $sisters = User::factory()->female()->count(3)->create(['is_enabled' => true]);

        $this->assertDatabaseCount('users', 7);

        $location = Location::factory()->create([
            'max_volunteers' => 3,
            'requires_brother' => true,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        $shiftId  = $shift->id;

        $date  = '2023-01-03'; // A Tuesday
        ShiftUser::factory()->create([
            'shift_id' => $shift->id,
            'user_id' => $sisters->get(0)->getKey(),
            'shift_date' => $date,
        ]);
        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);
        // 4 users in the system. Should have all 7 users returned
        $response->assertJsonCount(6, 'data');

        ShiftUser::factory()->create([
            'shift_id' => $shift->id,
            'user_id' => $sisters->get(1)->getKey(),
            'shift_date' => $date,
        ]);

        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);
        // 4 users in the system. Should only have male users returned
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
                    'date' => $date,
                    'do_reserve' => true,
                    'location' => $location->id,
                    'shift' => $shift->id,
                    'user' => $enabledUser->getKey(),
                ]
            )
            ->assertOk();

        $this->assertDatabaseCount('shift_user', 1);
        $this->assertDatabaseHas('shift_user', [
            'shift_id' => $shift->id,
            'user_id' => $enabledUser->getKey(),
            'shift_date' => $date,
        ]);

        $this->actingAs($admin)
            ->deleteJson("/admin/toggle-shift-for-user", [
                    'date' => $date,
                    'do_reserve' => false,
                    'location' => $location->id,
                    'shift' => $shift->id,
                    'user' => $enabledUser->getKey(),
                ]
            )->assertOk();

        $this->assertDatabaseCount('shift_user', 0);
        $this->assertDatabaseMissing('shift_user', [
            'shift_id' => $shift->id,
            'user_id' => $enabledUser->getKey(),
            'shift_date' => $date,
        ]);

        ray($disabledUser->getKey());
        // Test that adding an 'inactive' user fails
        $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date' => $date,
                    'do_reserve' => true,
                    'location' => $location->id,
                    'shift' => $shift->id,
                    'user' => $disabledUser->getKey(),
                ]
            )->assertStatus(422);
    }

    public function test_gender_restrictions_are_enforced(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $users  = User::factory()->female()->count(3)->create();

        $this->assertDatabaseCount('users', 4);

        $location = Location::factory()->create([
            'requires_brother' => true,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        $this->travelTo('2023-01-02 09:00:00');
        $date  = '2023-01-03'; // A Tuesday

        $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date' => $date,
                    'do_reserve' => true,
                    'location' => $location->id,
                    'shift' => $shift->id,
                    'user' => $users->get(0)->getKey(),
                ]
            )->assertOk();
        $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date' => $date,
                    'do_reserve' => true,
                    'location' => $location->id,
                    'shift' => $shift->id,
                    'user' => $users->get(1)->getKey(),
                ]
            )->assertOk();
        $this->actingAs($admin)
            ->putJson("/admin/toggle-shift-for-user", [
                    'date' => $date,
                    'do_reserve' => true,
                    'location' => $location->id,
                    'shift' => $shift->id,
                    'user' => $users->get(2)->getKey(),
                ]
            )->assertStatus(422);
    }
}
