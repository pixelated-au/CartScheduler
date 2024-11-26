<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

use function Laravel\Prompts\error;

class GetUserShiftReminderData
{
    /**
     * Query all users with shifts on the requested date.
     */

    public function execute(string $targetDate): Collection
    {
        //$query = "SELECT DISTINCT id, name, email, gender FROM users WHERE id IN (SELECT DISTINCT user_id FROM shift_user WHERE shift_date = :targetDate)";
        $query = "SELECT
            u.id AS user_id,
            u.name,
            u.email,
            u.gender,
            GROUP_CONCAT(
                CONCAT(su.shift_id, '|', s.start_time, '|', l.name) SEPARATOR ';'
            ) AS all_shifts
        FROM
            users u
        JOIN
            shift_user su
        ON
            u.id = su.user_id
        JOIN
            shifts s
        ON
            su.shift_id = s.id
        JOIN
            locations l
        ON
            s.location_id = l.id
        WHERE
            su.shift_date = :targetDate
        GROUP BY
            u.id, u.name, u.email, u.gender

        ";

        $params  = [':targetDate' => $targetDate];
        $results = DB::select($query, $params);
        error_log("targetDate: $targetDate");
        error_log("result = ");
        var_dump($results);
        //error_log("for date $targetDate");



        // filter results into an array of dicts/maps with id, name, gender and email per user.
        return collect($results)
            ->map(fn(stdClass $shift) => [
                'user_id'     => $shift->user_id,
                'user_email' => $shift->email,
                'user_name' => $shift->name,
                'user_gender' => $shift->gender,
                'shifts' => explode(";", $shift->all_shifts)
            ]);
    }
}
