<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class GetUserShiftsData
{
    /**
     * This is a recursive query that will return a list of dates from the start date
     * to the end date, and then join that list of dates to the shifts table to get
     * the shifts for that date.
     *
     * Parameters line up with those in cart-scheduler config
     *
     * @param string $startDate YYYY-MM-DD
     * @param string $endDate YYYY-MM-DD
     *
     * @return \Illuminate\Support\Collection
     */
    public function execute(string $startDate, string $endDate, User $user): Collection
    {
        $query = /** @lang MySQL */
            "
                WITH RECURSIVE dates (date) AS
                   (SELECT :startDate
                    UNION ALL
                    SELECT date + INTERVAL 1 DAY
                    FROM dates
                    WHERE date + INTERVAL 1 DAY <= :endDate)

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
                         INNER JOIN shift_user ON shift_user.shift_date = dates.date
                                              AND shift_user.user_id = :userId

                         INNER JOIN shifts ON shifts.is_enabled = TRUE
                                          AND shifts.id = shift_user.shift_id
                                          AND (
                                            shifts.available_from IS NULL
                                            OR shifts.available_from <= dates.date
                                           )
                                          AND (
                                            shifts.available_to IS NULL
                                            OR shifts.available_to >= dates.date
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

                         INNER JOIN locations ON locations.id = shifts.location_id
                                             AND locations.is_enabled = TRUE
                ORDER BY dates.date,
                         locations.name,
                         shifts.start_time
                ";

        $params = ['startDate' => $startDate, 'endDate' => $endDate, 'userId' => $user->id];

        $results = DB::select(DB::raw($query), $params);

        return collect($results)
            ->map(fn(stdClass $shift) => [
                'date_group'     => $shift->date,
                'shift_date'     => Carbon::parse("{$shift->date}T{$shift->start_time}"),
                'shift_id'       => $shift->shift_id,
                'volunteer_id'   => $shift->volunteer_id,
                'start_time'     => $shift->start_time,
                'location_id'    => $shift->location_id,
                'available_from' => $shift->available_from ? Carbon::parse($shift->available_from) : null,
                'available_to'   => $shift->available_to ? Carbon::parse($shift->available_to) : null,
                'max_volunteers' => (int)$shift->max_volunteers,
            ])
            ->filter(fn(array $shift) => $shift['volunteer_id'] === $user->id)
            ->groupBy([
                'date_group',
                'shift_id',
            ]);
    }
}
