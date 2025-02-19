<?php

namespace Tests\Feature\Integration\Actions;

use App\Actions\GetShiftFilledData;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetShiftFilledDataTest extends TestCase
{
    use RefreshDatabase;

    private GetShiftFilledData $getShiftFilledData;

    protected function setUp(): void
    {
        $this->getShiftFilledData = new GetShiftFilledData();
        parent::setUp();
    }

    public function test_filled_shifts_data_is_returning_correct_data(): void
    {

        $shiftId = Shift::factory()
            ->everyDay9am()
            ->for(Location::factory()->state(['max_volunteers' => 4]))
            ->create()->getKey();

        $userIds = User::factory()->count(3)->create()->map(fn(User $user) => $user->id);

        $dateRange = collect();
        collect(CarbonPeriod::create('2023-10-01', '2023-10-31')->toArray())
            ->map(fn(Carbon $date) => $dateRange->push(
                ...$userIds->map(fn(int $userId) => [
                'shift_date' => $date->format('Y-m-d'),
                'shift_id'   => $shiftId,
                'user_id'    => $userId,
                ])
            ));

        ShiftUser::factory()
            ->forEachSequence(...$dateRange->toArray())
            ->create();

        $this->travelTo('2023-10-01');

        // Test both fortnight and month
        collect([['count' => 14, 'period' => 'fortnight'], ['count' => 31, 'period' => 'month']])
            ->each(function (array $item) use ($dateRange) {
                $shiftFilledData = $this->getShiftFilledData->execute($item['period']);
                $this->assertCount($item['count'], $shiftFilledData);
                foreach ($shiftFilledData as $i => $iValue) {
                    // $i * 3 because the dates come in groups of 3
                    $this->assertSame($dateRange[$i * 3]['shift_date'], $iValue->date);
                    $this->assertSame(3, $iValue->shifts_filled);
                    $this->assertSame(4, $iValue->shifts_available);
                }
            });

        // Confirm the default period is month
        $shiftFilledData = $this->getShiftFilledData->execute();
        $this->assertCount(31, $shiftFilledData);
    }
}
