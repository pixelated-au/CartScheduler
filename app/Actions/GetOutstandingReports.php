<?php

namespace App\Actions;

use App\Data\OutstandingReportsData;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetOutstandingReports
{
    /**
     * @return Collection<int, OutstandingReportsData>
     */
    public function execute(?User $user = null): Collection
    {
        $reports = DB::query()
                 ->select([
                     'su.shift_id',
                     'su.shift_date',
                     'shifts.start_time',
                     'shifts.end_time',
                     'locations.requires_brother',
                     'locations.name as location_name',
                 ])
                 ->when($user !== null, fn(Builder $query) => $query->addSelect('su.user_id'))
                 ->from('shift_user', 'su')
                 ->leftJoin('shifts', 'shifts.id', '=', 'su.shift_id')
                 ->leftJoin('locations', 'locations.id', '=', 'shifts.location_id')
                 ->where('shift_date', '<=', Carbon::now()->format('Y-m-d'))
                 ->when($user !== null, fn(Builder $query) => $query->where('user_id', $user->id))
                 ->whereRaw('(SELECT COUNT(*)
                    FROM reports r
                    WHERE r.shift_id = su.shift_id
                    AND r.shift_date = su.shift_date) = 0')
                 ->when($user === null, fn(Builder $query) => $query->groupBy([
                     'su.shift_id',
                     'su.shift_date',
                     'shifts.start_time',
                     'shifts.end_time',
                     'locations.requires_brother',
                     'locations.name',
                 ]))
                 ->orderBy('shift_date')
                 ->orderBy('shifts.start_time')
                 ->get();

        return OutstandingReportsData::collect($reports);
    }
}
