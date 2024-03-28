<?php

namespace Tests\Feature\App;

use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\JsonAssertions;

class ShiftsTest extends TestCase
{
    use RefreshDatabase;
    use JsonAssertions;

    public function test_retrieve_shifts_over_three_weeks(): void
    {
        /*
         * February 2023
         * Mo Tu We Th Fr Sa Su
         *        1  2  3  4  5 <- When today is 2nd, can't get shifts for the 27th
         *  6  7  8  9 10 11 12 <- When today is 6th, can't get shifts for the 27th until 2:00 PM
         * 13 14 15 16 17 18 19
         * 20 21 22 23 24 25 26
         * 27 28
        */
        Config::set('cart-scheduler.shift_reservation_duration', 3); // 3 weeks
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', false); // Released once per week
        Config::set('cart-scheduler.release_weekly_shifts_on_day', 'MON'); // Monday
        Config::set('cart-scheduler.release_new_shifts_at_time', '14:00'); // 2:00 PM

        $startDate = CarbonImmutable::createFromTimeString('2023-02-02 12:30:00');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    , ['shift_date' => $startDate->addDay()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->addDay()->toDateString()}");
        $response->assertSuccessful();
        $response->assertJsonCount(1, 'shifts');
        $response->assertJsonCount(25, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-26']);
        $response->assertJsonMissingPath('freeShifts.2023-02-01');
        $response->assertJsonMissingPath('freeShifts.2023-02-27');
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-02', '2023-02-26');

        // Testing the new shift release before 2pm on Monday
        $this->travelTo($startDate->addDays(4)->setTimeFromTimeString('13:59:59'));
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->addDays(4)->toDateString()}");
        $response->assertJsonCount(0, 'shifts');
        $response->assertJsonCount(21, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-26']);
        $response->assertJsonMissingPath('freeShifts.2023-02-05');
        $response->assertJsonMissingPath('freeShifts.2023-02-27');
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-06', '2023-02-26');

        // Testing the new shift release after 2pm on Monday
        $this->travelTo($startDate->addDays(4)->setTimeFromTimeString('14:00:00'));
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->addDays(4)->toDateString()}");
        $response->assertJsonCount(0, 'shifts');
        $response->assertJsonCount(28, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-05']);
        $response->assertJsonMissingPath('freeShifts.2023-02-05');
        $response->assertJsonMissingPath('freeShifts.2023-03-06');
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-06', '2023-03-05');
    }

    public function test_one_week_only_retrieve_approved_shifts_after_set_time(): void
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
        Config::set('cart-scheduler.release_weekly_shifts_on_day', 'MON');
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:30'); // 12:30 PM

        $startDate = CarbonImmutable::createFromTimeString('2023-02-02 12:30:00');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    , ['shift_date' => $startDate->addDay()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;
        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->addDay()->toDateString()}");
        $response->assertJsonCount(1, 'shifts');
        $response->assertJsonCount(11, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-12']);
        $response->assertJsonMissingPath('freeShifts.2023-02-01');
        $response->assertJsonMissingPath('freeShifts.2023-02-13');
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-02', '2023-02-12');

        // Testing the new shift release before 12:30pm on Monday
        $this->travelTo($startDate->addDays(4)->setTimeFromTimeString('12:29:59'));
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->addDays(4)->toDateString()}");
        $response->assertJsonCount(0, 'shifts');
        $response->assertJsonCount(7, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-12']);
        $response->assertJsonMissingPath('freeShifts.2023-02-05');
        $response->assertJsonMissingPath('freeShifts.2023-02-13');
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-06', '2023-02-12');

        // Testing the new shift release after 12:30pm on Monday
        $this->travelTo($startDate->addDays(4)->setTimeFromTimeString('12:30:00'));
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->addDays(4)->toDateString()}");
        $response->assertJsonCount(0, 'shifts');
        $response->assertJsonCount(14, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-19']);
        $response->assertJsonMissingPath('freeShifts.2023-02-05');
        $response->assertJsonMissingPath('freeShifts.2023-02-20');
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-06', '2023-02-19');

        // Testing month crossover
        $this->travelTo($startDate->setDay(25)->midDay());
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->setDay(25)->toDateString()}");
        $response->assertJsonCount(0, 'shifts');
        $response->assertJsonCount(9, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-05']);
        $response->assertJsonMissingPath('freeShifts.2023-02-24');
        $response->assertJsonMissingPath('freeShifts.2023-03-06');
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-25', '2023-03-05');
    }

    public function test_user_cannot_retrieve_shifts_before_today(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-01-01 00:00:00');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        // request data from the previous month
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->subMonth()->setDay(15)->toDateString()}");
        $response->assertJsonCount(31, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-01-31']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-01', '2023-01-31');
        $response->assertJsonMissingPath('freeShifts.2022-12-31');
        $response->assertJsonMissingPath('freeShifts.2022-12-30');
    }

    public function test_user_cannot_retrieve_shifts_after_allowed_shift_timeframe(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-01-01 00:00:00');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        // request data for the next month - which is out of bounds
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->addMonth()->setDay(15)->toDateString()}");
        $response->assertJsonCount(31, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-01-31']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-01', '2023-01-31');
        $response->assertJsonMissingPath('freeShifts.2023-02-01');
        $response->assertJsonMissingPath('freeShifts.2023-02-02');
    }

    public function test_available_shifts_released_daily_for_month(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '00:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 00:00:00');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(31, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-14']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-15', '2023-02-14');
    }

    public function test_available_shifts_released_daily_for_month_after_time(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 11:59:59');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(30, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-13']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-15', '2023-02-13');

        $this->travelTo($startDate->setTimeFromTimeString('12:00:00'));
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(31, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-14']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-15', '2023-02-14');
    }

    public function test_available_shifts_released_daily_for_two_months(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 2);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '00:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 00:00:00');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(59, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-14']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-15', '2023-03-14');
    }

    public function test_available_shifts_released_daily_for_week(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '00:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 00:00:01');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(7, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-01-21']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-15', '2023-01-21');
    }

    public function test_available_shifts_released_daily_for_one_week_after_time(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 11:59:59');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(6, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-01-20']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-15', '2023-01-20');

        $this->travelTo($startDate->setTimeFromTimeString('12:00:00'));
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(7, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-01-21']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-15', '2023-01-21');

    }

    public function test_available_shifts_released_daily_for_three_weeks(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 3);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'WEEK');
        Config::set('cart-scheduler.do_release_shifts_daily', true);
        Config::set('cart-scheduler.release_new_shifts_at_time', '00:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 11:00:01');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(21, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-04']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-15', '2023-02-04');
    }

    public function test_available_shifts_released_once_per_month(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);

        $startDate = CarbonImmutable::createFromTimeString('2023-01-25 00:00:00');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(35, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-28']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-25', '2023-02-28');
    }

    public function test_available_shifts_released_once_per_month_at_a_time(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 1);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);
        Config::set('cart-scheduler.release_new_shifts_at_time', '12:00');

        $startDate = CarbonImmutable::createFromTimeString('2023-02-01 11:59:59');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(28, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-02-28']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-01', '2023-02-28');

        // Move to midday which should open up another month's worth of shifts
        $this->travelTo($startDate->midDay());
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(59, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-31']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-01', '2023-03-31');

        $this->travelTo($startDate->setDay(15)->midDay());
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(45, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-03-31']);
        $response->assertJsonMissingPath('freeShifts.2023-02-01');
        $response->assertJsonMissingPath('freeShifts.2023-02-14');
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-02-15', '2023-03-31');
    }

    public function test_available_shifts_released_beginning_of_month_for_three_month_duration(): void
    {
        Config::set('cart-scheduler.shift_reservation_duration', 3);
        Config::set('cart-scheduler.shift_reservation_duration_period', 'MONTH');
        Config::set('cart-scheduler.do_release_shifts_daily', false);

        $startDate = CarbonImmutable::createFromTimeString('2023-01-25 00:00:00');

        Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()
                ->everyDay9am()
                ->hasAttached(
                    User::factory()
                        ->userRoleUser()
                        ->count(3)
                        ->state(['is_enabled' => true])
                    // Add shifts so the system works; it doesn't show any free shifts if there's no shift in the system
                    , ['shift_date' => $startDate->subMonth()->toDateString()]
                )
            )
            ->create();

        $user = ShiftUser::with('user')->first()->user;

        $this->travelTo($startDate);
        $response = $this->actingAs($user)->getJson("/shifts/{$startDate->toDateString()}");
        $response->assertJsonCount(96, 'freeShifts');
        $response->assertJsonFragment(['maxDateReservation' => '2023-04-30']);
        $this->assertJsonHasKeys($response, 'freeShifts', '2023-01-25', '2023-04-30');
    }
}
