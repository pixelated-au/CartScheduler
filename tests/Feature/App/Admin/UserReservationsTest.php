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

    /*
     * future tests:
     * - admin can get a list of users - who are enabled
     * - users returned are only those who have assigned themselves to the particular shift
     * - admin can assign a user to a shift (reservation)
     * - test appropriate gender restrictions
    */

    public function test_admin_can_get_a_list_of_users(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $users = User::factory()->userRoleUser()->count(3)->create(['is_enabled' => true]);
        $firstUser = $users->first();
        $secondUser = $users->get(1);

        $location = Location::factory()->create([
            'requires_brother' => true,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->everyDay9am()->create([
            'location_id' => $location->id,
        ]);
        $date = '2023-01-03'; // A Tuesday

        $shiftId = $shift->id;
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
    public function test_list_of_users_doesnt_include_disabled(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
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
        $date = '2023-01-03'; // A Tuesday

        $shiftId = $shift->id;
        $response = $this->actingAs($admin)
            ->json('GET', "/admin/available-users-for-shift/$shiftId", ['date' => $date]);
        // 10 users in the system but should only have retrieve 5 active users because 5 are disabled
        $response->assertJsonCount(5, 'data');
        $response->assertJsonPath('data.0.id', $admin->getKey());
        $response->assertJsonPath('data.1.id', $enabledUser->getKey());
    }

    public function test_admin_can_assign_a_user_to_a_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_only_assign_users_who_are_enabled(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_gender_restrictions_are_enforced(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }
}
