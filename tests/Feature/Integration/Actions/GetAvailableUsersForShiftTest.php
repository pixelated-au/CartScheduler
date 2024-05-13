<?php

namespace Integration\Actions;

use App\Actions\GetAvailableUsersForShift;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetAvailableUsersForShiftTest extends TestCase
{
    use RefreshDatabase;

    private GetAvailableUsersForShift $getAvailableUsersForShift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getAvailableUsersForShift = $this->app->make(GetAvailableUsersForShift::class);
    }

    public function test_show_users_with_no_overlapping_shifts(): void
    {
        $locations = Location::factory()
            ->state(['max_volunteers' => 3])
            ->count(2)
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $locations->load('shifts');

        $users = User::factory()->count(5)->enabled()->create()->chunk(3);
        /** @var \Illuminate\Support\Collection $attachedUsers */
        $attachedUsers = $users->get(0);
        /** @var \Illuminate\Support\Collection $unattachedUsers */
        $unattachedUsers = $users->get(1);


        $dateRange = collect();
        $attachedUsers->chunk(3)
            ->get(0)
            ->each(fn(User $user) => $dateRange
                ->push([
                    'shift_date' => '2023-05-15',
                    'shift_id'   => $locations[0]->shifts[0]->id,
                    'user_id'    => $user->id,
                ])
            );

        ShiftUser::factory()
            ->forEachSequence(...$dateRange->toArray())
            ->create();

        // Note, 'un-available' users still exclude users who are on a shift at the same time - hence making sure the
        // true and false values have the same result
        $showOnlyAvailableValues = [true, false];
        foreach ($showOnlyAvailableValues as $showOnlyAvailable) {
            $availableUsers = $this->getAvailableUsersForShift->execute(
                shift: $locations[1]->shifts[0],
                date: Carbon::parse('2023-05-15'),
                showOnlyAvailable: $showOnlyAvailable,
                showOnlyResponsibleBros: false,
                hidePublishers: false,
                showOnlyElders: false,
                showOnlyMinisterialServants: false,
            );

            $this->assertCount($unattachedUsers->count(), $availableUsers);

            /** @var User $availableUser */
            foreach ($availableUsers as $availableUser) {
                $this->assertTrue($unattachedUsers->contains(fn(User $user) => $user->getKey() === $availableUser->getKey()));
            }
        }
    }

    public function test_show_only_responsible_brothers(): void
    {
        $locations = Location::factory()
            ->state(['max_volunteers' => 3])
            ->count(2)
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $locations->load('shifts');

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $users */
        $users = User::factory()->state(['responsible_brother' => false])->enabled()->count(5)->create()->chunk(3);

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $responsibleBros */
        $responsibleBros = User::factory()->responsibleBrother()->count(3)->create()->chunk(2);

        $attachedUsers  = $users->get(1)->merge($responsibleBros->get(1)); // 3 users, 1 of which is a responsible brother
        $remainingUsers = $users->get(0)->merge($responsibleBros->get(0)); // 5 users, 2 of which are responsible brothers


        $dateRange = collect();
        $attachedUsers->each(fn(User $user) => $dateRange
            ->push([
                'shift_date' => '2023-05-15',
                'shift_id'   => $locations[0]->shifts[0]->id,
                'user_id'    => $user->id,
            ])
        );

        ShiftUser::factory()
            ->forEachSequence(...$dateRange->toArray())
            ->create();

        $availableUsers = $this->getAvailableUsersForShift->execute(
            shift: $locations[1]->shifts[0],
            date: Carbon::parse('2023-05-15'),
            showOnlyAvailable: true,
            showOnlyResponsibleBros: true,
            hidePublishers: false,
            showOnlyElders: false,
            showOnlyMinisterialServants: false,
        );

        // Make sure all returned users are responsible brothers
        $this->assertCount($availableUsers->count(), $availableUsers->filter(fn(User $user) => $user->responsible_brother === true));

        /** @var User $availableUser */
        foreach ($availableUsers as $availableUser) {
            $this->assertTrue($remainingUsers->contains(fn(User $user) => $user->getKey() === $availableUser->getKey()));
        }
    }

    public function test_show_only_pioneers(): void
    {
        $locations = Location::factory()
            ->state(['max_volunteers' => 3])
            ->count(2)
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $locations->load('shifts');

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $publishers */
        $publishers = User::factory()->count(5)->publisher()->create()->chunk(3);

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $pioneers */
        $pioneers = User::factory()->pioneer()->count(3)->create()->chunk(2);

        $attachedUsers  = $publishers->get(1)->merge($pioneers->get(1)); // 3 users, 1 of which is a pioneer
        $remainingUsers = $publishers->get(0)->merge($pioneers->get(0)); // 5 users, 2 of which are pioneers

        $dateRange = collect();
        $attachedUsers->each(fn(User $user) => $dateRange
            ->push([
                'shift_date' => '2023-05-15',
                'shift_id'   => $locations[0]->shifts[0]->id,
                'user_id'    => $user->id,
            ])
        );

        ShiftUser::factory()
            ->forEachSequence(...$dateRange->toArray())
            ->create();

        $availableUsers = $this->getAvailableUsersForShift->execute(
            shift: $locations[1]->shifts[0],
            date: Carbon::parse('2023-05-15'),
            showOnlyAvailable: true,
            showOnlyResponsibleBros: false,
            hidePublishers: true,
            showOnlyElders: false,
            showOnlyMinisterialServants: false,
        );

        $this->assertCount($availableUsers->count(), $availableUsers->filter(fn(User $user) => $user->serving_as !== 'publisher'));

        /** @var User $availableUser */
        foreach ($availableUsers as $availableUser) {
            $this->assertTrue($remainingUsers->contains(fn(User $user) => $user->getKey() === $availableUser->getKey()));
        }
    }

    public function test_show_only_elders(): void
    {
        $locations = Location::factory()
            ->state(['max_volunteers' => 3])
            ->count(2)
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $locations->load('shifts');

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $elders */
        $elders = User::factory()->count(5)->elder()->create()->chunk(3);

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $nonElders */
        $nonElders = User::factory()->enabled()->state(['appointment' => null])->count(3)->create()->chunk(2);

        $attachedUsers  = $elders->get(1)->merge($nonElders->get(1)); // 3 users, 1 of which is an elder
        $remainingUsers = $elders->get(0)->merge($nonElders->get(0)); // 5 users, 2 of which are elders

        $dateRange = collect();
        $attachedUsers->each(fn(User $user) => $dateRange
            ->push([
                'shift_date' => '2023-05-15',
                'shift_id'   => $locations[0]->shifts[0]->id,
                'user_id'    => $user->id,
            ])
        );

        ShiftUser::factory()
            ->forEachSequence(...$dateRange->toArray())
            ->create();

        $availableUsers = $this->getAvailableUsersForShift->execute(
            shift: $locations[1]->shifts[0],
            date: Carbon::parse('2023-05-15'),
            showOnlyAvailable: true,
            showOnlyResponsibleBros: false,
            hidePublishers: false,
            showOnlyElders: true,
            showOnlyMinisterialServants: false,
        );

        $this->assertCount($availableUsers->count(), $availableUsers->filter(fn(User $user) => $user->appointment === 'elder'));

        /** @var User $availableUser */
        foreach ($availableUsers as $availableUser) {
            $this->assertTrue($remainingUsers->contains(fn(User $user) => $user->getKey() === $availableUser->getKey()));
        }
    }

    public function test_show_only_ministerial_servants(): void
    {
        $locations = Location::factory()
            ->state(['max_volunteers' => 3])
            ->count(2)
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $locations->load('shifts');

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $mSs */
        $mSs = User::factory()->count(5)->ministerialServant()->create()->chunk(3);

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $nonMSs */
        $nonMSs = User::factory()->enabled()->state(['appointment' => null])->count(3)->create()->chunk(2);

        $attachedUsers  = $mSs->get(1)->merge($nonMSs->get(1)); // 3 users, 1 of which is an MS
        $remainingUsers = $mSs->get(0)->merge($nonMSs->get(0)); // 5 users, 2 of which are MSs

        $dateRange = collect();
        $attachedUsers->each(fn(User $user) => $dateRange
            ->push([
                'shift_date' => '2023-05-15',
                'shift_id'   => $locations[0]->shifts[0]->id,
                'user_id'    => $user->id,
            ])
        );

        ShiftUser::factory()
            ->forEachSequence(...$dateRange->toArray())
            ->create();

        $availableUsers = $this->getAvailableUsersForShift->execute(
            shift: $locations[1]->shifts[0],
            date: Carbon::parse('2023-05-15'),
            showOnlyAvailable: true,
            showOnlyResponsibleBros: false,
            hidePublishers: false,
            showOnlyElders: false,
            showOnlyMinisterialServants: true,
        );

        $this->assertCount($availableUsers->count(), $availableUsers->filter(fn(User $user) => $user->appointment === 'ministerial servant'));

        /** @var User $availableUser */
        foreach ($availableUsers as $availableUser) {
            $this->assertTrue($remainingUsers->contains(fn(User $user) => $user->getKey() === $availableUser->getKey()));
        }
    }

    public function test_show_only_appointed_brothers(): void
    {
        $locations = Location::factory()
            ->state(['max_volunteers' => 4])
            ->count(2)
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $locations->load('shifts');

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $appointedBros */
        $appointedBros = User::factory()
            ->count(6)
            ->sequence(['appointment' => 'elder'], ['appointment' => 'ministerial servant'])
            ->create()
            ->chunk(3);

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $nonAppointed */
        $nonAppointed = User::factory()
            ->male()
            ->enabled()
            ->state(['appointment' => null])
            ->count(3)
            ->create()
            ->chunk(2);

        $attachedUsers  = $appointedBros->get(1)->merge($nonAppointed->get(1)); // 4 users, 3 of which are appointed brothers
        $remainingUsers = $appointedBros->get(0)->merge($nonAppointed->get(0)); // 5 users, 3 of which are appointed brothers

        $dateRange = collect();
        $attachedUsers->each(fn(User $user) => $dateRange
            ->push([
                'shift_date' => '2023-05-15',
                'shift_id'   => $locations[0]->shifts[0]->id,
                'user_id'    => $user->id,
            ])
        );

        ShiftUser::factory()
            ->forEachSequence(...$dateRange->toArray())
            ->create();

        $availableUsers = $this->getAvailableUsersForShift->execute(
            shift: $locations[1]->shifts[0],
            date: Carbon::parse('2023-05-15'),
            showOnlyAvailable: true,
            showOnlyResponsibleBros: false,
            hidePublishers: false,
            showOnlyElders: true,
            showOnlyMinisterialServants: true,
        );

        $this->assertCount($availableUsers->count(), $availableUsers->filter(
            fn(User $user) => $user->appointment === 'elder' || $user->appointment === 'ministerial servant')
        );

        /** @var User $availableUser */
        foreach ($availableUsers as $availableUser) {
            $this->assertTrue($remainingUsers->contains(fn(User $user) => $user->getKey() === $availableUser->getKey()));
        }
    }

    public function test_enable_all_user_filters(): void
    {
        $locations = Location::factory()
            ->state(['max_volunteers' => 5])
            ->count(2)
            ->has(Shift::factory()->everyDay9am())
            ->create();

        $locations->load('shifts');

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $nonRegular */
        $nonRegular = User::factory()
            ->count(8)
            ->responsibleBrother()
            ->pioneer()
            ->sequence(
                ['appointment' => 'elder'],
                ['appointment' => 'ministerial servant'],
            )
            ->create()
            ->chunk(4);

        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, User>> $regular */
        $regular = User::factory()
            ->male()
            ->publisher()
            ->state(['responsible_brother' => false, 'appointment' => null])
            ->count(3)
            ->create()
            ->chunk(2);

        $attachedUsers  = $nonRegular->get(1)->merge($regular->get(1)); // 4 users, 3 of which are appointed brothers
        $remainingUsers = $nonRegular->get(0)->merge($regular->get(0)); // 5 users, 3 of which are appointed brothers

        $dateRange = collect();
        $attachedUsers->each(fn(User $user) => $dateRange
            ->push([
                'shift_date' => '2023-05-15',
                'shift_id'   => $locations[0]->shifts[0]->id,
                'user_id'    => $user->id,
            ])
        );

        ShiftUser::factory()
            ->forEachSequence(...$dateRange->toArray())
            ->create();

        $availableUsers = $this->getAvailableUsersForShift->execute(
            shift: $locations[1]->shifts[0],
            date: Carbon::parse('2023-05-15'),
            showOnlyAvailable: true,
            showOnlyResponsibleBros: true,
            hidePublishers: true,
            showOnlyElders: true,
            showOnlyMinisterialServants: true,
        );

        $this->assertCount($availableUsers->count(),
            $availableUsers->filter(
                fn(User $user) => (
                        $user->appointment === 'elder'
                        || $user->appointment === 'ministerial servant'
                    )
                    && $user->serving_as !== 'publisher'
                    && $user->responsible_brother
            )
        );

        /** @var User $availableUser */
        foreach ($availableUsers as $availableUser) {
            $this->assertTrue($remainingUsers->contains(fn(User $user) => $user->getKey() === $availableUser->getKey()));
        }
    }
}
