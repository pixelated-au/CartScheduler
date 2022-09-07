<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissingReportsForUserController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return DB::query()
                 ->select([
                     'su.*',
                     'shifts.start_time',
                     'shifts.end_time',
                     'locations.requires_brother',
                     'locations.name as location_name'
                 ])
                 ->from('shift_user', 'su')
                 ->leftJoin('shifts', 'shifts.id', '=', 'su.shift_id')
                 ->leftJoin('locations', 'locations.id', '=', 'shifts.location_id')
                 ->where('shift_date', '<=', DB::raw('NOW()'))
                 ->where('user_id', $user->id)
                 ->whereRaw('
                    (SELECT COUNT(*)
                    FROM reports r
                    WHERE r.shift_id = su.shift_id
                    AND r.shift_date = su.shift_date) = 0
                  ')
                 ->orderBy('shift_date')
                 ->orderBy('shifts.start_time')
                 ->get()
                 ->filter(function ($val) use ($user) {
                     if ($val->requires_brother) {
                         return $user->gender === 'male';
                     }

                     return true;
                 });
    }
}
