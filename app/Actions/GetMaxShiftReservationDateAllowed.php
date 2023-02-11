<?php

namespace App\Actions;

use App\Enums\DBPeriod;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Carbon;

class GetMaxShiftReservationDateAllowed
{
    public function execute(): Carbon
    {
        $now                  = Carbon::now()->setTime(23, 59, 59);
        $period               = DBPeriod::getConfigPeriod();
        $duration             = config('cart-scheduler.shift_reservation_duration');
        $releaseShiftsOnDay   = config('cart-scheduler.release_weekly_shifts_on_day');
        $doReleaseShiftsDaily = config('cart-scheduler.do_release_shifts_daily');

        if ($doReleaseShiftsDaily) {
            if ($period->value === DBPeriod::Week->value) {
                return $now->addWeeks($duration)->endOfDay();
            }

            return $now->addMonths($duration)->endOfDay();
        }

        if ($period->value === DBPeriod::Week->value) {
            // Adding 1 to the duration because $now->startOfWeek(Carbon::SUNDAY) is the start of the week, so we're going back in time...
            return $now->startOfWeek($releaseShiftsOnDay - 1)->addWeeks($duration + 1)->endOfDay();
        }

        return $now->addMonths($duration)->endOfMonth()->endOfDay();
    }

    public function getFailMessage(): string
    {
        $period               = DBPeriod::getConfigPeriod();
        $duration             = config('cart-scheduler.shift_reservation_duration');
        $releaseShiftsOnDay   = config('cart-scheduler.release_weekly_shifts_on_day');
        $doReleaseShiftsDaily = config('cart-scheduler.do_release_shifts_daily');

        if ($doReleaseShiftsDaily) {
            if ($period->value === DBPeriod::Week->value) {
                return "Sorry, you can only reserve shifts up to $duration week(s) in advance.";
            }

            return "Sorry, you can only reserve shifts up to $duration month(s) in advance.";
        }

        if ($period->value === DBPeriod::Week->value) {
            return "Sorry, you can only reserve shifts up to $duration week(s) in advance, starting each {$this->mapDayOfWeek($releaseShiftsOnDay)}.";
        }

        return "Sorry, you can only reserve shifts up to $duration month(s) in advance, after this month";
    }

    private function mapDayOfWeek(int $dayOfWeek): string
    {
        --$dayOfWeek; // MySQL: Sunday starts on 1, Carbon: Sunday starts on 0

        return match ($dayOfWeek) {
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            default => throw new InvalidArgumentException('Invalid day of week'),
        };
    }
}
