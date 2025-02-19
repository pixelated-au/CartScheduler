<?php

namespace Tests\Feature\Integration\Actions;

use App\Actions\ValidateShiftIsAvailableAction;
use App\Exceptions\ShiftAvailabilityException;
use App\Models\Location;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ValidateShiftIsAvailableTest extends TestCase
{
    use RefreshDatabase;

    private ValidateShiftIsAvailableAction $validateShiftIsAvailableAction;

    protected function setUp(): void
    {
        $this->validateShiftIsAvailableAction = new ValidateShiftIsAvailableAction();
        parent::setUp();
    }

    public function test_dates_within_shift_availability_are_working(): void
    {
        $startDate = Carbon::createFromTimeString('2023-10-01T12:00:00');
        $endDate   = $startDate->clone()->endOfMonth();

        $location = Location::factory()->create();

        $shift = Shift::factory()
            ->everyDay9am()
            ->state([
                'available_from' => $startDate->toDateString(),
                'available_to'   => $endDate->toDateString()
            ])
            ->for($location)
            ->create();

        $this->expectNotToPerformAssertions();
        $this->validateShiftIsAvailableAction->execute($shift, $startDate);
        $this->validateShiftIsAvailableAction->execute($shift, $startDate->addDay());
        $this->validateShiftIsAvailableAction->execute($shift, $startDate->setDay(15));
        $this->validateShiftIsAvailableAction->execute($shift, $startDate->endOfMonth());
        $this->validateShiftIsAvailableAction->execute($shift, $startDate->subDay());
    }

    public function test_dates_before_and_after_shift_availability_are_failing(): void
    {
        $startDate = Carbon::createFromTimeString('2023-10-01T12:00:00');
        $endDate   = $startDate->clone()->endOfMonth();

        $location = Location::factory()->create();

        $shift = Shift::factory()
            ->everyDay9am()
            ->state([
                'available_from' => $startDate->toDateString(),
                'available_to'   => $endDate->toDateString()
            ])
            ->for($location)
            ->create();


        $this->assertThrows(
            fn() => $this->validateShiftIsAvailableAction->execute($shift, $startDate->clone()->subDay()),
            ShiftAvailabilityException::class,
            ShiftAvailabilityException::notAvailableYet()->getMessage()
        );

        $this->assertThrows(
            fn() => $this->validateShiftIsAvailableAction->execute($shift, $startDate->clone()->endOfMonth()->addDay()),
            ShiftAvailabilityException::class,
            ShiftAvailabilityException::notAvailableAnymore()->getMessage()
        );
    }
}
