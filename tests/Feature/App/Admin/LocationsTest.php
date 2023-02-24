<?php

namespace Tests\Feature\App\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_location_with_two_shifts(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_edit_location_and_add_extra_shift(): void
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
}
