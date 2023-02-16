<?php

namespace Tests\Feature\App;

use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\Tests\LocationAndShiftsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ShiftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_retrieve_approved_shifts_after_set_time(): void
    {
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
        Config::set('cart-scheduler.release_weekly_shifts_on_day', 2); // Monday
        Config::set('cart-scheduler.release_weekly_shifts_at_time', '12:30'); // 12:30 PM

        $user = User::factory()->create();
        $this->seed(LocationAndShiftsSeeder::class);
        $locationId = Location::first(['id'])->id;
        $shiftId    = Shift::inRandomOrder()->first(['id'])->id;

        $this->travelTo('2023-02-02 12:30:00');
        $response = $this->actingAs($user)->get('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(11, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-12']);
        $response->assertJsonMissingPath('shifts.2023-02-01');
        $response->assertJsonMissingPath('shifts.2023-02-13');

        $this->travelTo('2023-02-06 12:29:00');
        $response = $this->actingAs($user)->get('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(7, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-12']);
        $response->assertJsonMissingPath('shifts.2023-02-05');
        $response->assertJsonMissingPath('shifts.2023-02-13');

        $this->travelTo('2023-02-06 12:30:00');
        $response = $this->actingAs($user)->get('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(14, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-19']);
        $response->assertJsonMissingPath('shifts.2023-02-05');
        $response->assertJsonMissingPath('shifts.2023-02-20');

        $this->travelTo('2023-02-25 12:30:00');
        $response = $this->actingAs($user)->get('/shifts');
        $response->assertJsonCount(3);
        $response->assertJsonCount(9, 'shifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-05']);
        $response->assertJsonMissingPath('shifts.2023-02-24');
        $response->assertJsonMissingPath('shifts.2023-03-06');
    }
}
