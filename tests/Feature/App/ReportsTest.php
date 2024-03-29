<?php

namespace Tests\Feature\App;

use App\Models\Location;
use App\Models\Report;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Tags\Tag;
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
        $user = User::factory()->male()->create(['is_enabled' => true]);
        $tag  = Tag::findOrCreate('test_tag', 'reports');

        $shift = Shift::factory()->everyDay9am()->for(Location::factory())->create();

        $this->assertDatabaseCount('reports', 0);

        ShiftUser::factory()
            ->state(['shift_date' => '2023-01-01'])
            ->for($shift, 'shift')
            ->for($user, 'user')
            ->create();

        $reportData = [
            'shift_date'          => '2023-01-01',
            'shift_id'            => $shift->getKey(),
            'start_time'          => '09:00:00',
            'shift_was_cancelled' => false,
            'placements_count'    => 2,
            'videos_count'        => 3,
            'requests_count'      => 4,
            'comments'            => 'A test comment',
            'tags'                => [$tag->id],
        ];

        $this->actingAs($user)->postJson('/save-report', $reportData);

        $this->assertDatabaseCount('reports', 1);
        $report = Report::first();
        $this->assertEquals($reportData['shift_date'], $report->shift_date);
        $this->assertEquals($reportData['shift_id'], $report->shift_id);
        $this->assertEquals($reportData['shift_was_cancelled'], $report->shift_was_cancelled);
        $this->assertEquals($reportData['placements_count'], $report->placements_count);
        $this->assertEquals($reportData['videos_count'], $report->videos_count);
        $this->assertEquals($reportData['requests_count'], $report->requests_count);
        $this->assertEquals($reportData['comments'], $report->comments);
        $this->assertEquals($reportData['tags'][0], $report->tags->first()->id);
    }

    public function test_validate_sister_cannot_submit_report_if_brother_only_is_specified(): void
    {
        $user = User::factory()->female()->create(['is_enabled' => true]);
        $tag  = Tag::findOrCreate('test_tag', 'reports');

        $shift = Shift::factory()
            ->everyDay9am()
            ->for(Location::factory()->state(['requires_brother' => true]))
            ->create();

        $this->assertDatabaseCount('reports', 0);

        ShiftUser::factory()
            ->state(['shift_date' => '2023-01-01'])
            ->for($shift, 'shift')
            ->for($user, 'user')
            ->create();

        $reportData = [
            'shift_date'          => '2023-01-01',
            'shift_id'            => $shift->getKey(),
            'start_time'          => '09:00:00',
            'shift_was_cancelled' => false,
            'placements_count'    => 2,
            'videos_count'        => 3,
            'requests_count'      => 4,
            'comments'            => 'A test comment',
            'tags'                => [$tag->id],
        ];

        $response = $this->actingAs($user)->postJson('/save-report', $reportData);
        $response->assertStatus(422);
        $this->assertDatabaseCount('reports', 0);
    }
}
