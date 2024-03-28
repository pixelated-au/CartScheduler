<?php

namespace Tests\Feature\App;

use App\Models\Location;
use App\Models\Report;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\ExtraFunctions;

class ReportsTest extends TestCase
{
    use RefreshDatabase;
    use ExtraFunctions;

    public function test_user_receives_correct_reports(): void
    {
        $startDate = CarbonImmutable::createFromTimeString('2023-01-24 00:00:00');

        $users = User::factory()->count(3)->male()->create(['is_enabled' => true]);

        $dates = collect([
            ['shift_date' => '2023-01-01'],
            ['shift_date' => '2023-01-05'],
            ['shift_date' => '2023-01-06'],
            ['shift_date' => '2023-01-09'],
            ['shift_date' => '2023-01-11'],
            ['shift_date' => '2023-01-15'],
            ['shift_date' => '2023-01-20'],
            ['shift_date' => '2023-01-25'],
        ]);

        $shift = Shift::factory()->everyDay9am()->for(Location::factory())->create();
        foreach ($users as $user) {
            ShiftUser::factory()
                ->count($dates->count())
                ->sequence(...$dates->toArray())
                ->for($shift, 'shift')
                ->for($user, 'user')
                ->create();
        }

        $this->travelTo($startDate->midDay());
        $response = $this->actingAs($users[0])->getJson('/outstanding-reports');
        $response->assertJsonCount(7);
        $response->assertJsonPath('0.shift_date', '2023-01-01');
        $response->assertJsonPath('1.shift_date', '2023-01-05');
        $response->assertJsonPath('2.shift_date', '2023-01-06');
        $response->assertJsonPath('3.shift_date', '2023-01-09');
        $response->assertJsonPath('4.shift_date', '2023-01-11');
        $response->assertJsonPath('5.shift_date', '2023-01-15');
        $response->assertJsonPath('6.shift_date', '2023-01-20');

        Report::factory()
            ->count(4)
            ->sequence(...$dates->toArray())
            ->for($shift)
            ->for($users[0])
            ->create();

        $response = $this->actingAs($users[0])->getJson('/outstanding-reports');
        $response->assertJsonCount(3);
        $response->assertJsonPath('0.shift_date', '2023-01-11');
        $response->assertJsonPath('1.shift_date', '2023-01-15');
        $response->assertJsonPath('2.shift_date', '2023-01-20');

        $this->travelTo($startDate->setDay(25));
        $response = $this->actingAs($users[0])->getJson('/outstanding-reports');
        $response->assertJsonCount(4);
        $response->assertJsonPath('0.shift_date', '2023-01-11');
        $response->assertJsonPath('1.shift_date', '2023-01-15');
        $response->assertJsonPath('2.shift_date', '2023-01-20');
        $response->assertJsonPath('3.shift_date', '2023-01-25');
    }

    public function test_user_can_submit_report_with_tags(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_user_can_submit_canceled_shift(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }
}
