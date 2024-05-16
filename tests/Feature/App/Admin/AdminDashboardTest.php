<?php

namespace App\Admin;

use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;
use Tests\Traits\SetConfig;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;
    use SetConfig;

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->enabled()->create();
        $this->actingAs($user)
            ->get("/admin/")
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard_and_get_correct_data(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        $users = User::factory()->enabled()->count(4)->create()->chunk(2);

        $locations = Location::factory()
            ->count(2)
            ->allPublishers()
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached($users->first(), ['shift_date' => '2023-01-03'])
                    ->hasAttached($users->last(), ['shift_date' => '2023-01-04'])
            )
            ->has(Shift::factory()
                ->everyDay1230pm()
                ->hasAttached($users->first(), ['shift_date' => '2023-01-03'])
                ->hasAttached($users->last(), ['shift_date' => '2023-01-04'])
            )
            ->create();

        $this->travelTo('2023-01-03 09:00:00');

        $this->actingAs($admin)
            ->get("/admin/")
            ->assertOk()
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Admin/Dashboard')
                ->where('totalUsers', User::count())
                ->where('totalLocations', $locations->count())
                ->has('shiftFilledData', 14)
                ->has('outstandingReports', 4)
                ->has('outstandingReports', fn(AssertableInertia $data) => $data
                    ->whereContains('shift_date', '2023-01-03')
                    ->each(fn(AssertableInertia $data) => $data
                        ->whereNot('shift_date', '2023-01-04')
                        ->etc()
                    )
                    ->whereContains('start_time', '09:00:00')
                    ->whereContains('end_time', '12:00:00')
                    ->whereContains('start_time', '12:30:00')
                    ->whereContains('end_time', '15:30:00')
                    ->whereContains('location_name', $locations[0]->name)
                    ->whereContains('location_name', $locations[1]->name)
                    ->etc()
                )
            );

        $this->travelTo('2023-01-05 09:00:00');

        $this->actingAs($admin)
            ->get("/admin/")
            ->assertOk()
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Admin/Dashboard')
                ->where('totalUsers', User::count())
                ->where('totalLocations', $locations->count())
                ->has('shiftFilledData', 14)
                ->has('shiftFilledData', fn(AssertableInertia $data) => $data
                    ->where('0.date', '2023-01-05')
                    ->where('0.shifts_filled', 0)
                    ->where('0.shifts_available', 20)
                    ->where('13.date', '2023-01-18')
                    ->where('13.shifts_filled', 0)
                    ->where('13.shifts_available', 20)
                    ->etc()
                )
                ->has('outstandingReports', 8)
                ->has('outstandingReports', fn(AssertableInertia $data) => $data
                    ->whereContains('shift_date', '2023-01-03')
                    ->whereContains('shift_date', '2023-01-04')
                    ->whereContains('start_time', '09:00:00')
                    ->whereContains('end_time', '12:00:00')
                    ->whereContains('start_time', '12:30:00')
                    ->whereContains('end_time', '15:30:00')
                    ->whereContains('location_name', $locations[0]->name)
                    ->whereContains('location_name', $locations[1]->name)
                    ->etc()
                )
            );
    }

    public function test_admin_can_retrieve_correct_shift_data(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $users = User::factory()->count(4)->create()->chunk(2);

        $location = Location::factory()
            ->allPublishers()
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached($users->first(), ['shift_date' => '2023-01-03'])
                    ->hasAttached($users->last(), ['shift_date' => '2023-01-04'])
            )
            ->has(Shift::factory()
                ->everyDay1230pm()
                ->hasAttached($users->first(), ['shift_date' => '2023-01-03'])
                ->hasAttached($users->last(), ['shift_date' => '2023-01-04'])
            )
            ->create();

        $this->travelTo('2023-01-03 09:00:00');

        $this->actingAs($admin)
            ->getJson("/admin/assigned-shifts/2023-01-03")
            ->assertOk()
            ->assertJsonCount(31, 'freeShifts')
            ->assertJsonFragment([
                '2023-01-01' => ['volunteer_count' => 0, 'max_volunteers' => 10, 'has_availability' => true],
                '2023-01-02' => ['volunteer_count' => 0, 'max_volunteers' => 10, 'has_availability' => true],
                '2023-01-03' => ['volunteer_count' => 4, 'max_volunteers' => 10, 'has_availability' => true],
                '2023-01-04' => ['volunteer_count' => 4, 'max_volunteers' => 10, 'has_availability' => true],
                '2023-01-05' => ['volunteer_count' => 0, 'max_volunteers' => 10, 'has_availability' => true],
                '2023-01-06' => ['volunteer_count' => 0, 'max_volunteers' => 10, 'has_availability' => true],
            ])
            ->assertJsonCount(1, 'locations')
            ->assertJsonPath('locations.0.id', $location->id)
            ->assertJsonPath('locations.0.name', $location->name);


        $this->actingAs($admin)
            // Make sure we get the full months worth of data for the next month
            ->getJson("/admin/assigned-shifts/2023-02-15")
            ->assertOk()
            ->assertJsonCount(28, 'freeShifts')
            ->assertJsonFragment([
                '2023-02-01' => ['volunteer_count' => 0, 'max_volunteers' => 10, 'has_availability' => true],
                '2023-02-28' => ['volunteer_count' => 0, 'max_volunteers' => 10, 'has_availability' => true],
            ])
            ->assertJsonPath('freeShifts.2023-02-01.max_volunteers', 10)
            ->assertJsonPath('freeShifts.2023-02-28.max_volunteers', 10)
            ->assertJsonMissingPath('freeShifts.2023-01-31')
            ->assertJsonMissingPath('freeShifts.2023-02-29')
            ->assertJsonMissingPath('freeShifts.2023-03-01')
            ->assertJsonCount(1, 'locations')
            ->assertJsonPath('locations.0.id', $location->id)
            ->assertJsonPath('locations.0.name', $location->name);
    }

    public function test_invalid_date_returns_today(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);
        $users = User::factory()->count(4)->create()->chunk(2);

        $location = Location::factory()
            ->allPublishers()
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached($users->first(), ['shift_date' => '2023-01-03'])
                    ->hasAttached($users->last(), ['shift_date' => '2023-01-04'])
            )
            ->has(Shift::factory()
                ->everyDay1230pm()
                ->hasAttached($users->first(), ['shift_date' => '2023-01-03'])
                ->hasAttached($users->last(), ['shift_date' => '2023-01-04'])
            )
            ->create();

        $this->travelTo('2023-01-03 09:00:00');

        $this->actingAs($admin)
            ->getJson("/admin/assigned-shifts/2023-05-55") // This date doesn't exist
            ->assertOk()
            ->assertJsonCount(31, 'freeShifts')
            ->assertJsonPath('freeShifts.2023-01-01.max_volunteers', 10)
            ->assertJsonPath('freeShifts.2023-01-31.max_volunteers', 10)
            ->assertJsonCount(1, 'locations')
            ->assertJsonPath('locations.0.id', $location->id)
            ->assertJsonPath('locations.0.name', $location->name);
    }
}
