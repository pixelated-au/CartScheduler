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
        $query = DB::table('users as u')
            ->join('shift_user as su', 'u.id', '=', 'su.user_id')
            ->join('shifts as s', 'su.shift_id', '=', 's.id')
            ->join('locations as l', 's.location_id', '=', 'l.id')
            ->select(
                'u.id as user_id',
                'u.name',
                'u.email',
                'u.gender',
                DB::raw("GROUP_CONCAT(CONCAT(su.shift_id, '|', s.start_time, '|', l.name) SEPARATOR ';') as all_shifts")
            )
            ->where('su.shift_date', '=', $targetDate)
            ->groupBy('u.id', 'u.name', 'u.email', 'u.gender')
            ->get();
        var_dump($query);



        // filter results into an array of dicts/maps with id, name, gender and email per user.
        return collect($query)
            ->map(fn(stdClass $shift) => [
                'user_id'     => $shift->user_id,
                'user_email' => $shift->email,
                'user_name' => $shift->name,
                'user_gender' => $shift->gender,
                'shifts' => explode(";", $shift->all_shifts)
            ]);
    }
}
