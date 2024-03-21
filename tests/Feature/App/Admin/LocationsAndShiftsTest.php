<?php

namespace Tests\Feature\App\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationsAndShiftsTest extends TestCase
{
    use RefreshDatabase;
//TODO IMPLEMENT AND TEST CREATING A SHIFT AT THE SAME TIME AS AN EXISTING SHIFT BUT ONLY ALLOW IF THE EXISTING SHIFT IS DISABLED OR IS WITHIN THE 'ALLOWED' TIMEFRAME
// ALSO WITH THE ABOVE, MAKE SURE IF A SHIFT IS AVAILABLE AT THE SAME TIME, THEN A 'DISABLED' SHIFT CAN'T BE ENABLED
// TEST RESTRICTED VOLUNTEER FUNCTIONS

    public function test_admin_can_add_location_with_no_shifts(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_add_location_with_one_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_add_location_with_two_shifts(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_edit_location_and_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
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
