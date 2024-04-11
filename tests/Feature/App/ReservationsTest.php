<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature\App;

use App\Enums\DBPeriod;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetConfig;

class ReservationsTest extends TestCase
{
    use RefreshDatabase;
    use SetConfig;

    public function test_female_user_can_reserve_and_release(): void
    {
        $user = User::factory()->female()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        $location = Location::factory()
            ->state(['requires_brother' => false])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->assertDatabaseCount('shift_user', 0);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])->assertOk();

        $this->assertDatabaseCount('shift_user', 1);
        $this->assertDatabaseHas('shift_user', [
            'shift_id'   => $location->shifts[0]->id,
            'user_id'    => $user->getKey(),
            'shift_date' => $startDate->addDay()->toDateString(),
        ]);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => false,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertOk();

        $this->assertDatabaseCount('shift_user', 0);
        $this->assertDatabaseMissing('shift_user', [
            'shift_id'   => $location->shifts[0]->id,
            'user_id'    => $user->getKey(),
            'shift_date' => $startDate->addDay()->toDateString(),
        ]);
    }

    public function test_female_user_cannot_reserve_last_spot_of_shift_requiring_one_brother(): void
    {
        $user = User::factory()->female()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()
                            ->female()
                            ->count(2)
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('shift');

        $this->assertStringContainsStringIgnoringCase('needs to be a brother', $response->json('errors.shift.0'));
        $this->assertDatabaseCount('shift_user', 2);
    }

    public function test_male_user_can_reserve_and_release(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()
                            ->female()
                            ->count(2)
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])->assertOk();

        $this->assertDatabaseCount('shift_user', 3);
        $this->assertDatabaseHas('shift_user', [
            'shift_id'   => $location->shifts[0]->id,
            'user_id'    => $user->getKey(),
            'shift_date' => $startDate->addDay()->toDateString(),
        ]);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => false,
            'date'       => $startDate->addDay()->toDateString(),
        ])->assertOk();

        $this->assertDatabaseCount('shift_user', 2);
        $this->assertDatabaseMissing('shift_user', [
            'shift_id'   => $location->shifts[0]->id,
            'user_id'    => $user->getKey(),
            'shift_date' => $startDate->addDay()->toDateString(),
        ]);
    }

    public function test_not_enabled_user_cannot_reserve_shifts(): void
    {
        $user = User::factory()->state(['is_enabled' => false])->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->assertDatabaseCount('shift_user', 0);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])->assertUnauthorized();

        $this->assertDatabaseCount('shift_user', 0);
    }

    public function test_user_can_reserve_on_the_first_day_of_reservation_period(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 00:00:01');

        $this->travelTo($startDate);
        $location = Location::factory()
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->toDateString(),
        ])->assertOk();

        $this->assertDatabaseCount('shift_user', 1);
        $this->assertDatabaseHas('shift_user', [
            'shift_id'   => $location->shifts[0]->id,
            'user_id'    => $user->getKey(),
            'shift_date' => $startDate->toDateString(),
        ]);
    }

    public function test_user_can_reserve_last_day_of_reservation_period(): void
    {
        $this->setConfig(1, DBPeriod::Week, false, 'MON', '00:00');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-11 00:00:01'); // Wednesday

        $this->travelTo($startDate);
        $location = Location::factory()
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->setDay(15)->toDateString(), // set to the last day which is a Sunday
        ])->assertOk();

        $this->assertDatabaseCount('shift_user', 1);
        $this->assertDatabaseHas('shift_user', [
            'shift_id'   => $location->shifts[0]->id,
            'user_id'    => $user->getKey(),
            'shift_date' => $startDate->setDay(15)->toDateString(),
        ]);
    }

    public function test_user_cannot_reserve_day_before_today(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-11 00:00:01'); // Wednesday

        $this->travelTo($startDate);
        $location = Location::factory()
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->subDay()->toDateString(),
        ])->assertInvalid('date');
        $this->assertDatabaseCount('shift_user', 0);
    }

    public function test_user_cannot_reserve_day_after_last_day_of_reservation_period(): void
    {
        $this->setConfig(1, DBPeriod::Week, true, 'SUN', '00:00');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-11 00:00:01'); // Wednesday

        $this->travelTo($startDate);
        $location = Location::factory()
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addWeek()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('date');
        $this->assertDatabaseCount('shift_user', 0);
    }

    /**
     * Make sure a volunteer can't attach themselves to a shift that already maxed out with volunteers
     */
    public function test_user_cannot_reserve_full_shift(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()
                            ->male()
                            ->count(3)
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $response = $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])->assertUnprocessable();

        $this->assertSame(100, $response->json('error_code'));
        $this->assertDatabaseCount('shift_user', 3);
    }

    public function test_user_cannot_reserve_already_reserved_shift(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        $user
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->assertDatabaseCount('shift_user', 1);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('shift');

        $this->assertDatabaseCount('shift_user', 1);
    }

    public function test_male_user_can_reserve_shift_that_does_not_require_male_with_only_females_occupying(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => false, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()
                            ->female()
                            ->count(2)
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->assertDatabaseCount('shift_user', 2);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertOk();

        $this->assertDatabaseCount('shift_user', 3);
    }

    public function test_male_user_can_reserve_shift_that_does_require_male_with_only_females_occupying(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()
                            ->female()
                            ->count(2)
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->assertDatabaseCount('shift_user', 2);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertOk();

        $this->assertDatabaseCount('shift_user', 3);
    }

    public function test_male_user_can_reserve_shift_that_does_require_male_with_only_males_occupying(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()
                            ->male()
                            ->count(2)
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->assertDatabaseCount('shift_user', 2);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertOk();

        $this->assertDatabaseCount('shift_user', 3);
    }

    public function test_female_user_can_reserve_shift_that_does_not_require_male_with_only_females_occupying(): void
    {
        $user = User::factory()->female()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => false, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()
                            ->female()
                            ->count(2)
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->assertDatabaseCount('shift_user', 2);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])->assertOk();

        $this->assertDatabaseCount('shift_user', 3);
    }

    public function test_female_user_cannot_reserve_shift_that_requires_brother_with_only_females_occupying(): void
    {
        $user = User::factory()->female()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()
                            ->female()
                            ->count(2)
                        , ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->assertDatabaseCount('shift_user', 2);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('shift');

        $this->assertDatabaseCount('shift_user', 2);
    }

    public function test_user_cannot_reserve_at_inactive_location(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3, 'is_enabled' => false])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->assertDatabaseCount('shift_user', 0);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('location');

        $this->assertDatabaseCount('shift_user', 0);
    }

    public function test_user_cannot_reserve_a_disabled_shift(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->state(['is_enabled' => false])
                    ->everyDay9am()
            )
            ->create();

        $this->assertDatabaseCount('shift_user', 0);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('shift');

        $this->assertDatabaseCount('shift_user', 0);
    }

    public function test_user_cannot_reserve_a_shift_on_day_where_shift_is_not_enabled(): void
    {
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->state(['day_monday' => false])
            )
            ->create();

        $this->assertDatabaseCount('shift_user', 0);

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('shift');

        $this->assertDatabaseCount('shift_user', 0);
    }

    public function test_user_cannot_see_inactive_locations(): void
    {
        $this->setConfig(1, DBPeriod::Week, true, null, '12:00');
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        $locations = Location::factory()
            ->count(2)
            ->state(['max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()->count(2)->male(),
                        ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->assertDatabaseCount('locations', 2);
        $this->actingAs($user)->getJson("/shifts/{$startDate->addDay()->toDateString()}")
            ->assertOk()
            ->assertJsonCount(2, 'locations')
            ->assertJsonCount(7, 'freeShifts')
            ->assertJsonPath("freeShifts.{$startDate->toDateString()}.volunteer_count", fn(int $val) => $val === 0)
            ->assertJsonPath("freeShifts.{$startDate->toDateString()}.max_volunteers", fn(int $val) => $val === 6)
            ->assertJsonPath("freeShifts.{$startDate->addDay()->toDateString()}.volunteer_count", fn(int $val) => $val === 4)
            ->assertJsonPath("freeShifts.{$startDate->addDay()->toDateString()}.max_volunteers", fn(int $val) => $val === 6);

        // Disable 1 location. The assertions should halve...
        $locations[0]->is_enabled = false;
        $locations[0]->save();

        $this->actingAs($user)->getJson("/shifts/{$startDate->addDay()->toDateString()}")
            ->assertOk()
            ->assertJsonCount(1, 'locations')
            ->assertJsonCount(7, 'freeShifts')
            ->assertJsonPath("freeShifts.{$startDate->toDateString()}.volunteer_count", fn(int $val) => $val === 0)
            ->assertJsonPath("freeShifts.{$startDate->toDateString()}.max_volunteers", fn(int $val) => $val === 3)
            ->assertJsonPath("freeShifts.{$startDate->addDay()->toDateString()}.volunteer_count", fn(int $val) => $val === 2)
            ->assertJsonPath("freeShifts.{$startDate->addDay()->toDateString()}.max_volunteers", fn(int $val) => $val === 3);
    }

    public function test_user_cannot_see_disabled_shifts(): void
    {
        $this->setConfig(1, DBPeriod::Week, true, null, '12:00');
        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);

        $locations = Location::factory()
            ->count(2)
            ->state(['max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->count(1)
                    ->everyDay9am()
                    ->hasAttached(
                        User::factory()->count(2)->male(),
                        ['shift_date' => $startDate->addDay()->toDateString()]
                    )
            )
            ->create();

        $this->assertDatabaseCount('shifts', 2);

        $this->actingAs($user)->getJson("/shifts/{$startDate->addDay()->toDateString()}")
            ->assertOk()
            ->assertJsonCount(0, 'shifts')
            ->assertJsonCount(2, 'locations')
            ->assertJsonCount(1, 'locations.0.shifts')
            ->assertJsonCount(1, 'locations.1.shifts')
            ->assertJsonCount(7, 'freeShifts')
            ->assertJsonPath("freeShifts.{$startDate->toDateString()}.volunteer_count", fn(int $val) => $val === 0)
            ->assertJsonPath("freeShifts.{$startDate->toDateString()}.max_volunteers", fn(int $val) => $val === 6)
            ->assertJsonPath("freeShifts.{$startDate->addDay()->toDateString()}.volunteer_count", fn(int $val) => $val === 4)
            ->assertJsonPath("freeShifts.{$startDate->addDay()->toDateString()}.max_volunteers", fn(int $val) => $val === 6);

        // Disable 1 location. The assertions should halve...
        $locations[0]->shifts[0]->is_enabled = false;
        $locations[0]->shifts[0]->save();

        $this->actingAs($user)->getJson("/shifts/{$startDate->addDay()->toDateString()}")
            ->assertOk()
            ->assertJsonCount(0, 'shifts')
            ->assertJsonCount(2, 'locations')
            ->assertJsonCount(0, 'locations.0.shifts')
            ->assertJsonCount(1, 'locations.1.shifts')
            ->assertJsonCount(7, 'freeShifts')
            ->assertJsonPath("freeShifts.{$startDate->toDateString()}.volunteer_count", fn(int $val) => $val === 0)
            ->assertJsonPath("freeShifts.{$startDate->toDateString()}.max_volunteers", fn(int $val) => $val === 3)
            ->assertJsonPath("freeShifts.{$startDate->addDay()->toDateString()}.volunteer_count", fn(int $val) => $val === 2)
            ->assertJsonPath("freeShifts.{$startDate->addDay()->toDateString()}.max_volunteers", fn(int $val) => $val === 3);

    }

    public function test_user_cannot_reserve_daily_released_shifts_beyond_month(): void
    {
        $this->setConfig(1, DBPeriod::Month, true, null, '00:00');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-02-08 00:00:00');

        $this->travelTo($startDate);

        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addMonth()->subDay()->toDateString(),
        ])
            ->assertOk()
            ->assertContent('Reservation made');

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addMonth()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('date');
    }

    public function test_user_cannot_reserve_daily_released_at_time_shifts_beyond_month(): void
    {
        $this->setConfig(1, DBPeriod::Month, true, null, '12:00');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-02-08 00:00:00');

        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->travelTo($startDate);
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addMonth()->subDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('date');

        $this->travelTo($startDate->midDay());
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addMonth()->subDay()->toDateString(),
        ])
            ->assertOk()
            ->assertContent('Reservation made');
    }

    public function test_user_cannot_reserve_daily_released_shifts_beyond_week(): void
    {
        $this->setConfig(1, DBPeriod::Week, true, null, '00:00');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-02-16 00:00:00');

        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->travelTo($startDate);
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addWeek()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('date');

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addWeek()->subDay()->toDateString(),
        ])
            ->assertOk()
            ->assertContent('Reservation made');
    }

    public function test_user_cannot_reserve_daily_released_at_time_shifts_beyond_week(): void
    {
        $this->setConfig(1, DBPeriod::Week, true, null, '12:00');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-02-16 00:00:00');

        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->travelTo($startDate);
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addWeek()->subDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('date');

        $this->travelTo($startDate->midDay());
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addWeek()->subDay()->toDateString(),
        ])
            ->assertOk()
            ->assertContent('Reservation made');
    }

    public function test_user_cannot_reserve_period_shifts_released_shifts_beyond_allowed_time_with_monthly_release(): void
    {
        $this->setConfig(1, DBPeriod::Month, false, null, '00:00');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-02-08 00:00:00');

        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->travelTo($startDate);
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addMonths(2)->firstOfMonth()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('date');

        $this->travelTo($startDate->midDay());
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            // Used the syntax below to be a more obvious comparison to the above.
            'date'       => $startDate->addMonths(2)->firstOfMonth()->subDay()->toDateString(),
        ])
            ->assertOk()
            ->assertContent('Reservation made');
    }

    public function test_user_cannot_reserve_released_shifts_beyond_allowed_time_with_weekly_release(): void
    {
        /*
         * February 2023
         * Mo Tu We Th Fr Sa Su
         *        1  2  3  4  5
         *  6  7  8  9 10 11 12 <- Wednesday 8th
         * 13 14 15 16 17 18 19 <- 18th allowed; Sunday 19th & rest of the month, not allowed
         * 20 21 22 23 24 25 26
         * 27 28
        */
        $this->setConfig(1, DBPeriod::Week, false, 'SUN', '00:00');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-02-08 00:00:00');

        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->travelTo($startDate);
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->setDay(19)->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('date');

        $this->travelTo($startDate->midDay());
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->setDay(18)->toDateString(),
        ])
            ->assertOk()
            ->assertContent('Reservation made');
    }

    public function test_user_cannot_reserve_new_shifts_before_allowed_time(): void
    {
        /*
         * February 2023
         * Mo Tu We Th Fr Sa Su
         *        1  2  3  4  5
         *  6  7  8  9 10 11 12
         * 13 14 15 16 17 18 19 <- Date is Monday 13th; before 12:30, only up until 19th is allowed
         * 20 21 22 23 24 25 26 <- After 12:30, user can reserve until the 26th
         * 27 28
        */
        $this->setConfig(1, DBPeriod::Week, false, 'MON', '12:30');

        $user = User::factory()->male()->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-02-13 12:29:00');

        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->travelTo($startDate);
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->setDay(26)->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertInvalid('date');

        $this->travelTo($startDate->midDay()->addMinutes(30));
        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->setDay(26)->toDateString(),
        ])
            ->assertOk()
            ->assertContent('Reservation made');
    }
}
