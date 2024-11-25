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
        $query = "SELECT DISTINCT id, name, email, gender FROM users WHERE id IN (SELECT DISTINCT user_id FROM shift_user WHERE shift_date = :targetDate)";

        $params  = [':targetDate' => $targetDate];
        $results = DB::select($query, $params);
        //error_log("result = ");
        //var_dump($results);
        //error_log("for date $targetDate");

        // filter results into an array of dicts/maps with id, name, gender and email per user.
        return collect($results)
            ->map(fn(stdClass $shift) => [
                'user_id'     => $shift->id,
                'user_email' => $shift->email,
                'user_name' => $shift->name,
                'user_gender' => $shift->gender,
            ]);
    }
}
