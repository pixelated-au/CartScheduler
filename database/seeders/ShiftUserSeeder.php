<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ShiftUserSeeder extends Seeder
{
    private array $dates;

    public function run(): void
    {
        $this->generateDates();

        $locations = Location::with('shifts')->where('is_enabled', true)->get();

        $locations->each(function (Location $location) {
            $maxUsers = $location->max_volunteers;
            $shifts   = $location->shifts;
            /** @var \App\Models\Shift $shift */
            foreach ($shifts as $shift) {
                $users = collect();
                $dates = $this->mapDates($shift);
                foreach ($dates as $date) {
                    for ($i = 0; $i < $maxUsers; $i++) {
                        $userId = User::inRandomOrder()->first()->id;
                        if ($users->search($userId) !== false) {
                            $i--;
                            continue;
                        }

                        $users->push($userId);
                        $shift->users()->attach($userId, ['shift_date' => $date]);
                    }
                }
            }
        });
    }

    private function mapDates(Shift $shift): Collection
    {
        $dates = collect();
        if ($shift->day_monday) {
            $dates = $dates->concat($this->dates['monday']);
        }
        if ($shift->day_tuesday) {
            $dates = $dates->concat($this->dates['tuesday']);
        }
        if ($shift->day_wednesday) {
            $dates = $dates->concat($this->dates['wednesday']);
        }
        if ($shift->day_thursday) {
            $dates = $dates->concat($this->dates['thursday']);
        }
        if ($shift->day_friday) {
            $dates = $dates->concat($this->dates['friday']);
        }
        if ($shift->day_saturday) {
            $dates = $dates->concat($this->dates['saturday']);
        }
        if ($shift->day_sunday) {
            $dates = $dates->concat($this->dates['sunday']);
        }

        return $dates;
    }

    private function generateDates(): void
    {
        $days  = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dates = [];
        foreach ($days as $day) {
            $dates[$day] = collect(new DatePeriod(
                Carbon::parse("first $day of this month"),
                CarbonInterval::week(),
                Carbon::parse("first $day of next month")
            ))->map(static fn($date) => $date->format('Y-m-d'))->toArray();
        }
        $this->dates = $dates;
    }

}