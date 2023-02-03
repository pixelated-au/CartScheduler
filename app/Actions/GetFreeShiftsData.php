<?php

namespace App\Actions;

use App\Enums\DBPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;

class GetFreeShiftsData
{
    /**
     * This is a recursive query that will return a list of dates from the start date
     * to the end date, and then join that list of dates to the shifts table to get
     * the shifts for that date.
     *
     * Parameters line up with those in cart-scheduler config
     *
     * @param string   $startDate YYYY-MM-DD
     * @param DBPeriod $period month, week, day
     * @param int      $durationLength any number that corresponds to the duration
     * @param int      $releaseShiftsOnDay the day of the week to release the shifts. 1 for Sunday, 2 for Monday, etc
     * @param bool     $doReleaseShiftsDaily - if true, the shifts will be released each period. Eg if the period is month, and the duration is 1, then the 1 month of shifts will be released on the first of each month.
     *
     * @return \Illuminate\Support\Collection
     */
    public function execute(
        string $startDate,
        DBPeriod $period,
        int $durationLength,
        int $releaseShiftsOnDay,
        bool $doReleaseShiftsDaily): Collection
    {
        if ($releaseShiftsOnDay < 1 || $releaseShiftsOnDay > 7) {
            throw new InvalidArgumentException('release_shifts_on_day must be between 1 and 7');
        }
        $interval = "$durationLength $period->value";

        $lastDay = $this->getDurationQuery($interval, $doReleaseShiftsDaily, $period, $releaseShiftsOnDay);
        ray($lastDay);

        $query = DB::raw(/** @lang MySQL */ "
                WITH RECURSIVE dates (date) AS
                   (SELECT :startDate
                    UNION ALL
                    SELECT date + INTERVAL 1 DAY
                    FROM dates
                    WHERE date + INTERVAL 1 DAY <= ($lastDay))

                SELECT dates.date,
                       shift_user.shift_date,
                       shift_user.user_id AS volunteer_id,
                       shifts.id AS shift_id,
                       shifts.start_time,
                       shifts.location_id,
                       shifts.available_from,
                       shifts.available_to,
                       locations.max_volunteers,
                       locations.name

                FROM dates
                         LEFT JOIN shifts ON shifts.is_enabled = true
                                          AND (
                                            shifts.available_from IS NULL
                                            OR shifts.available_from <= :startDate
                                           )
                                          AND (
                                            shifts.available_to IS NULL
                                            OR shifts.available_to >= :startDate
                                          )
                                          AND CASE
                                            WHEN DAYOFWEEK(dates.date) = 1 THEN shifts.day_sunday
                                            WHEN DAYOFWEEK(dates.date) = 2 THEN shifts.day_monday
                                            WHEN DAYOFWEEK(dates.date) = 3 THEN shifts.day_tuesday
                                            WHEN DAYOFWEEK(dates.date) = 4 THEN shifts.day_wednesday
                                            WHEN DAYOFWEEK(dates.date) = 5 THEN shifts.day_thursday
                                            WHEN DAYOFWEEK(dates.date) = 6 THEN shifts.day_friday
                                            WHEN DAYOFWEEK(dates.date) = 7 THEN shifts.day_saturday
                                            END = 1

                         LEFT JOIN shift_user ON shifts.id = shift_user.shift_id
                                              AND shift_user.shift_date = dates.date

                         LEFT JOIN locations ON locations.id = shifts.location_id
                                             AND locations.is_enabled = true
                ORDER BY dates.date,
                         locations.name,
                         shifts.start_time
                ",
        );

        $results = DB::select($query, ['startDate' => $startDate]);

        return collect($results)
            ->map(fn(stdClass $shift) => collect([
                'shift_date'     => $shift->date,
                'shift_id'       => $shift->shift_id,
                'volunteer_id'   => $shift->volunteer_id,
                'start_time'     => $shift->start_time,
                'location_id'    => $shift->location_id,
                'available_from' => $shift->available_from ? Carbon::parse($shift->available_from) : null,
                'available_to'   => $shift->available_to ? Carbon::parse($shift->available_to) : null,
                'max_volunteers' => (int)$shift->max_volunteers,
            ]))
            ->groupBy([
                'shift_date',
                'shift_id',
            ])
            ->sortKeys();
    }

    protected function getDurationQuery(
        string $interval,
        bool $doReleaseShiftsDaily,
        DBPeriod $period,
        int $releaseShiftsOnDay): string
    {
        $lastDay = "SELECT DATE_ADD(:startDate, INTERVAL $interval)";
        if (!$doReleaseShiftsDaily) {
            if ($period === DBPeriod::Month) {
                $lastDay = "SELECT LAST_DAY(DATE_ADD(:startDate, INTERVAL $interval))";
            } elseif ($period === DBPeriod::Week) {
                /**
                 * This releases the shifts on the specified day of the week plus 1 week.
                 *
                 * @noinspection SpellCheckingInspection
                 */
                $lastDay = "select DATE_ADD(DATE_ADD(:startDate, INTERVAL ($releaseShiftsOnDay - DAYOFWEEK(:startDate)) DAY ), INTERVAL 2 WEEK)";
            }
        }

        return $lastDay;
    }
}
