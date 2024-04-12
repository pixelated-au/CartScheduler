<?php

namespace Tests\Feature\App;

use App\Actions\ErrorApiResource;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestrictedVolunteerTest extends TestCase
{
    use RefreshDatabase;

    public function test_restricted_user_cannot_reserve(): void
    {
        $user = User::factory()->enabled()->state(['is_unrestricted' => false])->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        $location = Location::factory()
            ->state(['max_volunteers' => 3])
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => true,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertJsonPath('error_code', ErrorApiResource::CODE_NOT_ALLOWED)
            ->assertUnprocessable();

        $this->assertDatabaseEmpty('shift_user');
    }

    public function test_user_cannot_release(): void
    {
        $user = User::factory()->enabled()->state(['is_unrestricted' => false])->create();

        $startDate = CarbonImmutable::createFromTimeString('2023-01-15 12:00:00');

        $this->travelTo($startDate);
        /** @var Location $location */
        $location = Location::factory()
            ->state(['requires_brother' => true, 'max_volunteers' => 3])
            ->has(
                Shift::factory()
                    ->everyDay9am()
                    ->hasAttached($user, ['shift_date' => $startDate->addDay()->toDateString()])
            )
            ->create();

        $this->actingAs($user)->postJson('/reserve-shift', [
            'location'   => $location->id,
            'shift'      => $location->shifts[0]->id,
            'do_reserve' => false,
            'date'       => $startDate->addDay()->toDateString(),
        ])
            ->assertJsonPath('error_code', ErrorApiResource::CODE_NOT_ALLOWED)
            ->assertUnprocessable();

        $this->assertDatabaseCount('shift_user', 1);
    }
}
