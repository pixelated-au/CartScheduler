<?php

namespace Tests\Feature\App;

use App\Models\User;
use Database\Seeders\Tests\LocationAndShiftsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\JsonAssertions;

class ShiftsTest extends TestCase
{
    use RefreshDatabase;
    use JsonAssertions;

    public function test_retrieve_shifts_for_three_weeks(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        /*
         * February 2023
         * Mo Tu We Th Fr Sa Su
         *        1  2  3  4  5 <- When today is 2nd, can't get shifts for the 27th
         *  6  7  8  9 10 11 12 <- When today is 6nd, can't get shifts for the 27th until 2:00 PM
         * 13 14 15 16 17 18 19
         * 20 21 22 23 24 25 26
         * 27 28
        */
        Config::set('cart-scheduler.shift_reservation_duration', 3); // 3 weeks
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', false); // Released once per week
        Config::set('cart-scheduler.release_weekly_shifts_on_day', 'MON'); // Monday
        Config::set('cart-scheduler.release_new_shifts_at_time', '14:00'); // 2:00 PM

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);

        $this->travelTo('2023-02-02 12:30:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertSuccessful();
        $response->assertJsonCount(3);
        $response->assertJsonCount(25, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-26']);
        $response->assertJsonMissingPath('shifts.2023-02-01');
        $response->assertJsonMissingPath('shifts.2023-02-27');

        $this->assertJsonHasKeys($response, 'shifts', '2023-02-02', '2023-02-26');

        $this->travelTo('2023-02-06 13:59:59');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(21, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-26']);
        $response->assertJsonMissingPath('shifts.2023-02-05');
        $response->assertJsonMissingPath('shifts.2023-02-27');

        $this->assertJsonHasKeys($response, 'shifts', '2023-02-06', '2023-02-26');

        $this->travelTo('2023-02-06 14:00:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(28, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-05']);
        $response->assertJsonMissingPath('shifts.2023-02-05');
        $response->assertJsonMissingPath('shifts.2023-03-06');

        $this->assertJsonHasKeys($response, 'shifts', '2023-02-06', '2023-03-05');
    }

    public function test_one_week_only_retrieve_approved_shifts_after_set_time(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        /*
         * February 2023
         * Mo Tu We Th Fr Sa Su
         *        1  2  3  4  5
         *  6  7  8  9 10 11 12 <- When today is 6th, can't get shifts for the 19th until 12:30 PM
         * 13 14 15 16 17 18 19
         * 20 21 22 23 24 25 26
         * 27 28
        */
        Config::set('cart-scheduler.shift_reservation_duration', 1); // 1 week
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', false); // Released once per week
        Config::set('cart-scheduler.release_weekly_shifts_on_day', 'MON');
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:30'); // 12:30 PM

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);

        $this->travelTo('2023-02-02 12:30:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(11, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-12']);
        $response->assertJsonMissingPath('shifts.2023-02-01');
        $response->assertJsonMissingPath('shifts.2023-02-13');

        $this->assertJsonHasKeys($response, 'shifts', '2023-02-02', '2023-02-12');

        $this->travelTo('2023-02-06 12:29:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(7, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-12']);
        $response->assertJsonMissingPath('shifts.2023-02-05');
        $response->assertJsonMissingPath('shifts.2023-02-13');

        $this->assertJsonHasKeys($response, 'shifts', '2023-02-06', '2023-02-12');

        $this->travelTo('2023-02-06 12:30:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(14, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-19']);
        $response->assertJsonMissingPath('shifts.2023-02-05');
        $response->assertJsonMissingPath('shifts.2023-02-20');

        $this->assertJsonHasKeys($response, 'shifts', '2023-02-06', '2023-02-19');

        $this->travelTo('2023-02-25 12:30:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(9, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-05']);
        $response->assertJsonMissingPath('shifts.2023-02-24');
        $response->assertJsonMissingPath('shifts.2023-03-06');

        $this->assertJsonHasKeys($response, 'shifts', '2023-02-25', '2023-03-05');
    }

    public function test_available_shifts_released_daily_for_month(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        // Set the date and time to create predictable tests
        $this->travelTo('2023-01-15 00:00:00');
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '00:00');

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(31, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-14']);

        $this->assertJsonHasKeys($response, 'shifts', '2023-01-15', '2023-02-14');
    }

    public function test_available_shifts_released_daily_for_month_after_time(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        // Set the date and time to create predictable tests
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:00');

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $this->travelTo('2023-01-15 11:59:55');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(30, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-13']);
        $this->assertJsonHasKeys($response, 'shifts', '2023-01-15', '2023-02-13');

        $this->travelTo('2023-01-15 12:00:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(31, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-14']);
        $this->assertJsonHasKeys($response, 'shifts', '2023-01-15', '2023-02-14');
    }

    public function test_available_shifts_released_daily_for_two_months(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        // Set the date and time to create predictable tests
        $this->travelTo('2023-01-15 00:00:00');
        Config::set('cart-scheduler.shift_reservation_duration', 2);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '00:00');

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $response = $this->actingAs($user)->getJson('/shifts');

        $response->assertJsonCount(59, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-14']);

        $this->assertJsonHasKeys($response, 'shifts', '2023-01-15', '2023-03-14');
    }

    public function test_available_shifts_released_daily_for_week(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        $this->travelTo('2023-01-15 00:00:01');
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '00:00');

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $response = $this->actingAs($user)->getJson('/shifts');

        // When viewing the locations, the user should only see shifts for today plus 7 days (1 week + 1 day)
        $response->assertJsonCount(7, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-01-21']);

        $this->assertJsonHasKeys($response, 'shifts', '2023-01-15', '2023-01-21');
    }

    public function test_available_shifts_released_daily_for_one_week_after_time(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:00');

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);

        $this->travelTo('2023-01-15 11:59:55');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(6, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-01-20']);
        $this->assertJsonHasKeys($response, 'shifts', '2023-01-15', '2023-01-20');

        $this->travelTo('2023-01-15 12:00:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(7, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-01-21']);
        $this->assertJsonHasKeys($response, 'shifts', '2023-01-15', '2023-01-21');
    }

    public function test_available_shifts_released_daily_for_three_weeks(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        $this->travelTo('2023-01-15 00:00:01');
        Config::set('cart-scheduler.shift_reservation_duration', 3);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '00:00');

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $response = $this->actingAs($user)->getJson('/shifts');

        // When viewing the locations, the user should only see shifts for today plus 7 days (1 week + 1 day)
        $response->assertJsonCount(21, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-04']);

        $this->assertJsonHasKeys($response, 'shifts', '2023-01-15', '2023-02-04');
    }

    public function test_available_shifts_released_once_per_month(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        // Set the date and time to create predictable tests
        $this->travelTo('2023-01-25 00:00:00');
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $response = $this->actingAs($user)->getJson('/shifts');

        // When viewing the locations, the user should only see shifts for today plus the rest of the month plus 1 month
        $response->assertJsonCount(35, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-28']);

        $this->assertJsonHasKeys($response, 'shifts', '2023-01-25', '2023-02-28');
    }

    public function test_available_shifts_released_once_per_month_at_a_time(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        // Set the date and time to create predictable tests
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:00');

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);

        $this->travelTo('2023-02-01 11:59:55');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(28, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-28']);
        $this->assertJsonHasKeys($response, 'shifts', '2023-02-01', '2023-02-28');

        $this->travelTo('2023-02-01 12:00:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(59, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-31']);
        $this->assertJsonHasKeys($response, 'shifts', '2023-02-01', '2023-03-31');

        $this->travelTo('2023-02-15 12:00:00');
        $response = $this->actingAs($user)->getJson('/shifts');
        $response->assertJsonCount(45, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-31']);
        $response->assertJsonMissingPath('shifts.2023-02-01');
        $response->assertJsonMissingPath('shifts.2023-02-14');
        $this->assertJsonHasKeys($response, 'shifts', '2023-02-15', '2023-03-31');
    }

    public function test_available_shifts_released_beginning_of_month_for_three_month_duration(): void
    {
        $this->markTestSkipped('This test has been deprecated due to the change in the way shifts are retrieved');
        // TODO UPDATE THIS
        // Set the date and time to create predictable tests
        $this->travelTo('2023-01-25 00:00:00');
        Config::set('cart-scheduler.shift_reservation_duration', 3);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $response = $this->actingAs($user)->getJson('/shifts');

        // When viewing the locations, the user should only see shifts for today plus the rest of the month plus 1 month
        $response->assertJsonCount(96, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-04-30']);

        $this->assertJsonHasKeys($response, 'shifts', '2023-01-25', '2023-04-30');
    }
}
