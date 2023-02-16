<?php

namespace App\Actions;

use App\Enums\DBPeriod;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Carbon;

class GetMaxShiftReservationDateAllowed
{
    private DBPeriod $period;
    private mixed    $duration;
    private mixed    $releaseShiftsOnDay;
    private mixed    $releaseShiftsAtTime;
    private mixed    $doReleaseShiftsDaily;

    public function __construct()
    {
        $this->period               = DBPeriod::getConfigPeriod();
        $this->duration             = config('cart-scheduler.shift_reservation_duration');
        $this->releaseShiftsOnDay   = config('cart-scheduler.release_weekly_shifts_on_day');
        $this->releaseShiftsAtTime  = config('cart-scheduler.release_weekly_shifts_at_time');
        $this->doReleaseShiftsDaily = config('cart-scheduler.do_release_shifts_daily');
    }

    public function execute(): Carbon
    {
        $now = Carbon::now();

        if ($this->doReleaseShiftsDaily) {
            if ($this->period->value === DBPeriod::Week->value) {
                return $now->addWeeks($this->duration)->endOfDay();
            }

            return $now->addMonths($this->duration)->endOfDay();
        }

        if ($this->period->value === DBPeriod::Week->value) {
            return $now->startOfWeek($this->releaseShiftsOnDay - 1)
                       ->when(
                           fn(Carbon $date) => $this->isCurrentDayButAfterTime($date),
                           fn(Carbon $date) => $date->addWeeks($this->duration),
                           fn(Carbon $date) => $date->addWeeks($this->duration + 1),
                       )
                       ->subDay()
                       ->endOfDay();
        }

        return $now->addMonths($this->duration)->endOfMonth()->endOfDay();
    }

    public function getFailMessage(): string
    {
        $period               = DBPeriod::getConfigPeriod();
        $duration             = config('cart-scheduler.shift_reservation_duration');
        $releaseShiftsOnDay   = config('cart-scheduler.release_weekly_shifts_on_day');
        $releaseShiftsAtTime  = config('cart-scheduler.release_weekly_shifts_at_time');
        $doReleaseShiftsDaily = config('cart-scheduler.do_release_shifts_daily');

        if ($doReleaseShiftsDaily) {
            if ($period->value === DBPeriod::Week->value) {
                return "Sorry, you can only reserve shifts up to $duration week(s) in advance.";
            }

            return "Sorry, you can only reserve shifts up to $duration month(s) in advance.";
        }

        if ($period->value === DBPeriod::Week->value) {
            $time = Carbon::now()->setTimeFromTimeString($releaseShiftsAtTime)->format('g:i A');

            return "Sorry, you can only reserve shifts up to $duration week(s) in advance, starting each {$this->mapDayOfWeek($releaseShiftsOnDay)} at $time";
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

    /**
     * Determine if the given date is the current day but after the given time.
     * For example, if the current day is Monday and the given time is 10:00 AM,
     * this method will return true if the given date is Monday at 10:00 AM or later.
     */
    private function isCurrentDayButAfterTime(Carbon $date): bool
    {
        return $date->startOfWeek($this->releaseShiftsOnDay - 1)->isSameDay($date)
               && $date->setTimeFromTimeString($this->releaseShiftsAtTime)->isFuture();
    }
}
