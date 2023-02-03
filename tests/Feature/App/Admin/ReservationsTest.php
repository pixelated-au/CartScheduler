<?php

namespace Tests\Feature\App\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_move_user_to_another_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_cannot_move_female_user_to_another_shift_with_only_females_occupying(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }
}
