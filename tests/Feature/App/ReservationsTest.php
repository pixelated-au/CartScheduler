<?php

namespace Tests\Feature\App;

use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Database\Seeders\Tests\LocationAndShiftsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * For date-time testing:
 *
 * @see https://laravel.com/docs/9.x/mocking#interacting-with-time
 */
class ReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_female_user_can_reserve_and_release(): void
    {
        $user = User::factory()->female()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;
        $date       = date('Y-m-d', strtotime('tomorrow'));

        // Confirm that the DB is empty first
        $this->assertDatabaseMissing('shift_user', [
            'shift_id'   => $shiftId,
            'user_id'    => $user->getKey(),
            'shift_date' => $date,
        ]);

        $response = $this->actingAs($user)->post('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => $date,
        ]);

        $response->assertOk();

        // Confirm that the DB is updated
        $this->assertDatabaseHas('shift_user', [
            'shift_id'   => $shiftId,
            'user_id'    => $user->getKey(),
            'shift_date' => $date,
        ]);
    }

    public function test_male_user_can_reserve_and_release(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_can_reserve_and_release_first_day_of_month(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_can_reserve_and_release_last_day_of_next_month(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_reserve_day_before_first_day_of_month(): void
    {
        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;
        $date       = date('Y-m-d', strtotime('3 months ago'));

        $response = $this->actingAs($user)->post('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => $date,
        ]);

        //Note, you can do a dd($response) to see the response or any other variable
        //dd($response);
        // Or you can use $this->withoutExceptionHandling(); to see full error messages
        $response->assertInvalid();

        $this->assertDatabaseMissing('shift_user', [
            'shift_id'   => $shiftId,
            'user_id'    => $user->getKey(),
            'shift_date' => $date,
        ]);
    }

    public function test_user_cannot_reserve_day_after_last_day_of_next_month(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_reserve_one_week_after_last_day_of_next_month(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_reserve_full_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_reserve_already_reserved_shift(): void
    {
        // User is already on shift. Shouldn't be able to reserve it again.
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_male_user_can_reserve_shift_that_does_not_require_male_with_only_females_occupying(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_male_user_can_reserve_shift_that_does_require_male_with_only_females_occupying(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_male_user_can_reserve_shift_that_does_require_male_with_only_males_occupying(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_female_user_can_reserve_shift_that_does_not_require_male_with_only_females_occupying(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_female_user_cannot_reserve_shift_that_requires_brother_with_only_females_occupying(): void
    {
        $users = User::factory()->female()->count(3)->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;
        $date       = date('Y-m-d', strtotime('tomorrow'));

        ShiftUser::factory()->create([
            'shift_id'   => $shiftId,
            'user_id'    => $users[0]->getKey(),
            'shift_date' => $date,
        ]);

        ShiftUser::factory()->create([
            'shift_id'   => $shiftId,
            'user_id'    => $users[1]->getKey(),
            'shift_date' => $date,
        ]);

        $response = $this->actingAs($users[2])->post('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => $date,
        ]);
        // TODO this is failing because it's not implemented. I'll fix it asap - Ian.
        $response->assertInvalid();

        $this->assertDatabaseMissing('shift_user', [
            'shift_id'   => $shiftId,
            'user_id'    => $users[2]->getKey(),
            'shift_date' => $date,
        ]);

    }

    public function test_user_cannot_reserve_at_inactive_location(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_reserve_a_disabled_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_reserve_a_shift_on_wrong_day(): void
    {
        // Eg, a shift that is only on Mondays, but the user tries to reserve it on a Tuesday.
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_reserve_a_shift_on_invalid_date(): void
    {
        // Eg, a shift available between 1st and 15th of the month, but the user tries to reserve it on the 16th.
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_see_inactive_locations(): void
    {
        // When viewing the locations, the user shouldn't be able to see inactive locations.
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_cannot_see_disabled_shifts(): void
    {
        // When viewing the locations, the user shouldn't be able to see disabled shifts.
        $this->markTestSkipped('Not implemented yet.');
    }
}
