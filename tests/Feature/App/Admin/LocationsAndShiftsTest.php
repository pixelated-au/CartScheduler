<?php

namespace Tests\Feature\App\Admin;

use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationsAndShiftsTest extends TestCase
{
    use RefreshDatabase;

//TODO TEST RESTRICTED VOLUNTEER FUNCTIONS

    public function test_admin_can_add_location_with_no_shifts(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        $response = $this->actingAs($admin)
            ->postJson('/admin/locations', [
                'name'             => 'A test city',
                'description'      => 'lorem ipsum dolor sit amet',
                'min_volunteers'   => 2,
                'max_volunteers'   => 3,
                'requires_brother' => true,
                'is_enabled'       => true,
                'shifts'           => [],
            ]);
        $response->assertRedirect('http://localhost/admin/locations/1/edit');
        $this->assertDatabaseCount('locations', 1);
    }

    public function test_admin_can_add_location_with_one_shift(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        $response = $this->actingAs($admin)
            ->postJson('/admin/locations', [
                'name'             => 'A test city',
                'description'      => '<p>lorem ipsum dolor sit amet</p>',
                'min_volunteers'   => 2,
                'max_volunteers'   => 3,
                'requires_brother' => true,
                'is_enabled'       => true,
                'shifts'           => [
                    // Note there are multiple shifts so we're using an array containing an array of shift(...s)
                    [
                        'day_monday'     => true,
                        'day_tuesday'    => true,
                        'day_wednesday'  => false,
                        'day_thursday'   => true,
                        'day_friday'     => true,
                        'day_saturday'   => true,
                        'day_sunday'     => true,
                        'start_time'     => '10:00:00',
                        'end_time'       => '13:30:00',
                        'is_enabled'     => true,
                        'available_from' => null,
                        'available_to'   => null,
                    ]
                ],
            ]);
        $response->assertRedirect('http://localhost/admin/locations/1/edit');
        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseCount('shifts', 1);
        $this->assertDatabaseHas('shifts', [
            'day_monday'     => 1,
            'day_tuesday'    => 1,
            'day_wednesday'  => 0,
            'day_thursday'   => 1,
            'day_friday'     => 1,
            'day_saturday'   => 1,
            'day_sunday'     => 1,
            'start_time'     => "10:00:00",
            'end_time'       => "13:30:00",
            'is_enabled'     => 1,
            'location_id'    => 1,
            'available_from' => null,
            'available_to'   => null,
        ]);
    }

    public function test_admin_can_add_location_with_two_shifts(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        $shift = [
            'day_monday'     => true,
            'day_tuesday'    => true,
            'day_wednesday'  => false,
            'day_thursday'   => true,
            'day_friday'     => true,
            'day_saturday'   => true,
            'day_sunday'     => true,
            'start_time'     => '10:00:00',
            'end_time'       => '13:30:00',
            'is_enabled'     => true,
            'available_from' => null,
            'available_to'   => null,
        ];


        $response = $this->actingAs($admin)
            ->postJson('/admin/locations', [
                'name'             => 'A test city',
                'description'      => '<p>lorem ipsum dolor sit amet</p>',
                'min_volunteers'   => 2,
                'max_volunteers'   => 3,
                'requires_brother' => true,
                'is_enabled'       => true,
                'shifts'           => [
                    $shift,
                    [
                        ...$shift,
                        'day_wednesday'  => true,
                        'day_saturday'   => false,
                        'start_time'     => '14:00:00',
                        'end_time'       => '17:30:00',
                        'available_from' => '2021-01-01',
                        'available_to'   => '2021-01-31',
                    ],
                ],
            ]);
        $response->assertRedirect('http://localhost/admin/locations/1/edit');
        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseCount('shifts', 2);
        $this->assertDatabaseHas('shifts', [
            'day_wednesday'  => 0,
            'day_saturday'   => 1,
            'start_time'     => '10:00:00',
            'end_time'       => '13:30:00',
            'available_from' => null,
            'available_to'   => null,
        ]);
        $this->assertDatabaseHas('shifts', [
            'day_wednesday'  => 1,
            'day_saturday'   => 0,
            'start_time'     => '14:00:00',
            'end_time'       => '17:30:00',
            'available_from' => '2021-01-01 00:00:00',
            'available_to'   => '2021-01-31 23:59:59',
        ]);
    }

    public function test_admin_can_edit_location_and_shift(): void
    {
        $admin = User::factory()->adminRoleUser()->create(['is_enabled' => true]);

        $location = Location::factory()
            ->state([
                'name'             => 'A test city',
                'description'      => '<p>lorem ipsum dolor sit amet</p>',
                'min_volunteers'   => 2,
                'max_volunteers'   => 3,
                'requires_brother' => true,
                'is_enabled'       => false,
            ])
            ->has(Shift::factory()
                ->state([
                    'day_monday'     => true,
                    'day_tuesday'    => true,
                    'day_wednesday'  => false,
                    'day_thursday'   => true,
                    'day_friday'     => true,
                    'day_saturday'   => true,
                    'day_sunday'     => true,
                    'start_time'     => '10:00:00',
                    'end_time'       => '13:30:00',
                    'is_enabled'     => false,
                    'available_from' => null,
                    'available_to'   => null,
                ])
            )
            ->create();

        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseHas('locations', [
            'name'             => 'A test city',
            'max_volunteers'   => 3,
            'requires_brother' => 1,
            'is_enabled'       => 0,
        ]);


        $this->assertDatabaseCount('shifts', 1);
        $this->assertDatabaseHas('shifts', [
            'day_wednesday' => 0,
            'day_sunday'    => 1,
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/admin/locations/$location->id", [
                'name'             => 'A test city!', // changed
                'description'      => '<p>lorem ipsum dolor sit amet</p>',
                'min_volunteers'   => 2,
                'max_volunteers'   => 4, // changed
                'requires_brother' => false, // changed
                'is_enabled'       => true, // changed
                'shifts'           => [
                    [
                        'id'       => $location->shifts[0]->id,
                        'day_monday'     => true,
                        'day_tuesday'    => true,
                        'day_wednesday'  => true, // changed
                        'day_thursday'   => true,
                        'day_friday'     => true,
                        'day_saturday'   => true,
                        'day_sunday'     => false, // changed
                        'start_time'     => '10:00:00',
                        'end_time'       => '13:30:00',
                        'is_enabled'     => true,
                        'available_from' => null,
                        'available_to'   => null,
                    ],
                ]
            ]);

        $response->assertRedirect('http://localhost/admin/locations/1/edit');
        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseHas('locations', [
            'name'             => 'A test city!',
            'max_volunteers'   => 4,
            'requires_brother' => 0,
            'is_enabled'       => 1,
        ]);


        $this->assertDatabaseCount('shifts', 1);
        $this->assertDatabaseHas('shifts', [
            'day_wednesday' => 1,
            'day_sunday'    => 0,
        ]);
    }

    public function test_admin_can_add_location_with_restricted_timeframe_availability_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_add_extra_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_edit_shift_days(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_edit_shift_time(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_create_new_shift_time(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_delete_a_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_set_an_available_from_date(): void
    {
        // Verify that the shift is not available before the date
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_set_an_available_to_date(): void
    {
        // Verify that the shift is not available after the date
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_set_an_available_from_and_to_date(): void
    {
        // Verify that the shift is not available before and after the date
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_cannot_add_overlapping_enabled_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_add_overlapping_disabled_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_cannot_edit_shift_and_make_it_overlap_with_another_available_shift(): void
    {
        // test disabled and timeframe restricted shifts
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_edit_shift_and_make_it_overlap_with_another_unavailable_shift(): void
    {
        // test disabled and timeframe restricted shifts
        $this->markTestSkipped('Not implemented yet.');
    }
}
