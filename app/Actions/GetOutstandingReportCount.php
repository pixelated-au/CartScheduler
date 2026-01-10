<?php

namespace App\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetOutstandingReportCount
{
    public function execute(): int
    {
        return DB::query()
            ->from('shift_user', 'su')
            ->where('shift_date', '<=', Carbon::now()->format('Y-m-d'))
            ->whereRaw('(SELECT COUNT(*)
                    FROM reports r
                    WHERE r.shift_id = su.shift_id
                    AND r.shift_date = su.shift_date) = 0')
            ->groupBy([
                'su.shift_id',
                'su.shift_date',
            ])
            ->count();
    }
}
