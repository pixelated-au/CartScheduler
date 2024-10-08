<?php

namespace Database\Factories;

use App\Enums\AvailabilityHours;
use App\Models\UserAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UserAvailabilityFactory extends Factory
{
    protected $model = UserAvailability::class;

    protected Collection $dayParts;

    public function definition(): array
    {
        $this->dayParts = collect(AvailabilityHours::cases());

        $monday    = $this->availability();
        $tuesday   = $this->availability();
        $wednesday = $this->availability();
        $thursday  = $this->availability();
        $friday    = $this->availability();
        $saturday  = $this->availability();
        $sunday    = $this->availability();

        return [
            'day_monday'     => $monday,
            'day_tuesday'    => $tuesday,
            'day_wednesday'  => $wednesday,
            'day_thursday'   => $thursday,
            'day_friday'     => $friday,
            'day_saturday'   => $saturday,
            'day_sunday'     => $sunday,
            'num_mondays'    => $monday ? fake()->numberBetween(0, 4) : 0,
            'num_tuesdays'   => $tuesday ? fake()->numberBetween(0, 4) : 0,
            'num_wednesdays' => $wednesday ? fake()->numberBetween(0, 4) : 0,
            'num_thursdays'  => $thursday ? fake()->numberBetween(0, 4) : 0,
            'num_fridays'    => $friday ? fake()->numberBetween(0, 4) : 0,
            'num_saturdays'  => $saturday ? fake()->numberBetween(0, 4) : 0,
            'num_sundays'    => $sunday ? fake()->numberBetween(0, 4) : 0,
            'comments'       => fake()->sentence(),
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ];
    }

    public function wedThuTenToOne(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'day_monday'     => null,
            'day_tuesday'    => null,
            'day_wednesday'  => $this->getHourRange(10, 13),
            'day_thursday'   => $this->getHourRange(10, 13),
            'day_friday'     => null,
            'day_saturday'   => null,
            'day_sunday'     => null,
            'num_mondays'    => 0,
            'num_tuesdays'   => 0,
            'num_wednesdays' => 2,
            'num_thursdays'  => 1,
            'num_fridays'    => 0,
            'num_saturdays'  => 0,
            'num_sundays'    => 0,
        ]);
    }

    private function availability(): ?array
    {
        // 20% chance of full-day
        if (fake()->boolean(20)) {
            return $this->getHourRange(7, 18);
        }

        // 20% chance of morning
        if (fake()->boolean(20)) {
            return $this->getHourRange(7, 12);
        }

        // 20% chance of afternoon
        if (fake()->boolean(20)) {
            return $this->getHourRange(12, 18);
        }
        return null;
    }

    /**
     * @return AvailabilityHours[]
     */
    public function getHourRange(int $start, int $end): array
    {
        return $this->dayParts
            ->filter(fn(AvailabilityHours $availability) => $availability->value >= $start && $availability->value <= $end)
            ->toArray();
    }

}
