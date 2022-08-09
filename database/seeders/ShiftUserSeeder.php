<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShiftUserSeeder extends Seeder
{
    public function run(): void
    {
        $locations = Location::with('shifts')->where('is_enabled', true)->get();

        $locations->each(function (Location $location) {
            $maxUsers = $location->max_volunteers;
            $shifts   = $location->shifts;
            /** @var \App\Models\Shift $shift */
            foreach ($shifts as $shift) {
                $users = collect();
                $days  = $this->mapDays($shift);
                foreach ($days as $day) {
                    for ($i = 0; $i < $maxUsers; $i++) {
                        $userId = User::inRandomOrder()->first()->id;
                        if ($users->search($userId) !== false) {
                            $i--;
                            continue;
                        }

                        $users->push($userId);
                        $shift->users()->attach($userId, ['day' => $day]);
                    }
                }
            }
        });
    }

    private function mapDays(Shift $shift): array
    {
        $days = [];
        if ($shift->day_monday) {
            $days[] = 'monday';
        }
        if ($shift->day_tuesday) {
            $days[] = 'tuesday';
        }
        if ($shift->day_wednesday) {
            $days[] = 'wednesday';
        }
        if ($shift->day_thursday) {
            $days[] = 'thursday';
        }
        if ($shift->day_friday) {
            $days[] = 'friday';
        }
        if ($shift->day_saturday) {
            $days[] = 'saturday';
        }
        if ($shift->day_sunday) {
            $days[] = 'sunday';
        }

        return $days;
    }
}
