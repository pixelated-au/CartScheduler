<?php

namespace Tests\Feature\App;

use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Database\Seeders\Tests\LocationAndShiftsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
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
        $date       = date('Y-m-d');

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
        $response->assertInvalid();

        $this->assertDatabaseCount('shift_user', 2);

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

    public function test_user_cannot_reserve_daily_released_shifts_beyond_month(): void
    {
        // Set the date and time to create predictable tests (Wednesday)
        $this->travelTo('2023-02-08 00:00:00');
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', true);

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-03-08',
        ]);

        // The user can only reserve shifts for today plus ~30/31 days (1 month)
        $response->assertOk();
        $this->assertEquals('Reservation made', $response->content());
    }

    public function test_user_cannot_reserve_daily_released_shifts_beyond_week(): void
    {
        // Set the date and time to create predictable tests (Wednesday)
        $this->travelTo('2023-02-16 00:00:00');
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', true);

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-02-24', // 8 days away
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-02-23', // 7 days away
        ]);

        // The user can only reserve shifts for today plus 7 days (1 week)
        $response->assertOk();
        $this->assertEquals('Reservation made', $response->content());
    }

    public function test_user_cannot_reserve_period_shifts_released_shifts_beyond_two_months(): void
    {
        // Set the date and time to create predictable tests (Wednesday)
        $this->travelTo('2023-02-08 00:00:00');
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-04-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-03-31',
        ]);

        // The user can only reserve shifts for today plus the rest of the month plus 1 month
        $response->assertOk();
        $this->assertEquals('Reservation made', $response->content());
    }

    public function test_user_cannot_reserve_period_shifts_released_shifts_beyond_two_weeks(): void
    {
        /*
         * February 2023
         * Mo Tu We Th Fr Sa Su
         *        1  2  3  4  5
         *  6  7  8  9 10 11 12 <- Wednesday 8th
         * 13 14 15 16 17 18 19 <- Yes, allowed. Sunday 19th & rest of month, not allowed
         * 20 21 22 23 24 25 26
         * 27 28
        */
        // Set the date and time to create predictable tests (Wednesday)
        $this->travelTo('2023-02-08 03:00:00');
        Config::set('cart-scheduler.shift_reservation_duration', 1); // 1 week
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', false); // Released once per week
        Config::set('cart-scheduler.release_weekly_shifts_on_day', 1); // Sunday
        Config::set('cart-scheduler.release_weekly_shifts_at_time', '00:00'); // Midnight

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-02-19',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-02-18', // 3 days of rest of current week plus 7 days of next week
        ]);

        // The user can only reserve shifts for today plus the rest of the week plus 1 week
        $response->assertOk();
        $this->assertEquals('Reservation made', $response->content());
    }

    public function test_user_cannot_reserve_new_shifts_before_allowed_time(): void
    {
        /*
         * February 2023
         * Mo Tu We Th Fr Sa Su
         *        1  2  3  4  5
         *  6  7  8  9 10 11 12 <- When date is 13th, can't reserve after the 26th until 12:30 PM
         * 13 14 15 16 17 18 19
         * 20 21 22 23 24 25 26
         * 27 28
        */
        // Set the date and time to create predictable tests (Wednesday)
        Config::set('cart-scheduler.shift_reservation_duration', 1); // 1 week
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', false); // Released once per week
        Config::set('cart-scheduler.release_weekly_shifts_on_day', 2); // Monday
        Config::set('cart-scheduler.release_weekly_shifts_at_time', '12:30'); // 12:30 PM

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;

        $this->travelTo('2023-02-13 12:29:00');
        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-02-26',
        ]);

        // Note, should not be able to reserve this shift at 12:29 PM
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');

        $this->travelTo('2023-02-13 12:30:00');
        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $locationId,
            'shift'      => $shiftId,
            'do_reserve' => true,
            'date'       => '2023-02-26',
        ]);

        // This should work because the time is set to 12:30 PM which is on/after the release time
        $response->assertOk();
        $this->assertEquals('Reservation made', $response->content());
    }
}
