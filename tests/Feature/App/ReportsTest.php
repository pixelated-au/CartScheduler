<?php

namespace Tests\Feature\App;

use App\Models\Report;
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\Tests\LocationAndShiftsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\ExtraFunctions;

class ReportsTest extends TestCase
{
    use RefreshDatabase;
    use ExtraFunctions;

    public function test_user_receives_correct_reports(): void
    {
        // Create some shifts
        // Create some completed reports
        // Create some incomplete reports
        // Verify that the user receives the correct reports

        $users = User::factory()->count(3)->create(['gender' => 'male']);
        $this->seed(LocationAndShiftsSeeder::class);
        $shiftId = Shift::inRandomOrder()->first(['id'])->id;
        $dates   = [
            '2023-01-01',
            '2023-01-05',
            '2023-01-06',
            '2023-01-09',
            '2023-01-11',
            '2023-01-15',
            '2023-01-20',
            '2023-01-25',
        ];

        foreach ($dates as $date) {
            foreach ($users as $user) {
                $this->attachUserToShift($shiftId, $user, $date);
            }
        }
        $this->travelTo('2023-01-24 12:00:00');
        $response = $this->actingAs($users[0])->getJson('/outstanding-reports');
        $response->assertJsonCount(7);
        $this->assertSame($response[0]['shift_date'], '2023-01-01');
        $this->assertSame($response[1]['shift_date'], '2023-01-05');
        $this->assertSame($response[2]['shift_date'], '2023-01-06');
        $this->assertSame($response[3]['shift_date'], '2023-01-09');
        $this->assertSame($response[4]['shift_date'], '2023-01-11');
        $this->assertSame($response[5]['shift_date'], '2023-01-15');
        $this->assertSame($response[6]['shift_date'], '2023-01-20');

        // Only create reports for the first two dates
        for ($i = 0; $i < 4; $i++) {
            Report::factory()->create([
                'shift_id'                 => $shiftId,
                'report_submitted_user_id' => $users[0]->id,
                'shift_date'               => $dates[$i],
            ]);
        }

        $response = $this->actingAs($users[0])->getJson('/outstanding-reports');
        $response->assertJsonCount(3);
        $this->assertSame($response[0]['shift_date'], '2023-01-11');
        $this->assertSame($response[1]['shift_date'], '2023-01-15');
        $this->assertSame($response[2]['shift_date'], '2023-01-20');

        $this->travelTo('2023-01-25 12:00:00');
        $response = $this->actingAs($users[0])->getJson('/outstanding-reports');
        $response->assertJsonCount(4);
        $this->assertSame($response[0]['shift_date'], '2023-01-11');
        $this->assertSame($response[1]['shift_date'], '2023-01-15');
        $this->assertSame($response[2]['shift_date'], '2023-01-20');
        $this->assertSame($response[3]['shift_date'], '2023-01-25');
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
