<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum DBPeriod: string
{
    case Month = 'MONTH';
    case Months = 'MONTHS';
    case Week = 'WEEK';
    case Weeks = 'WEEKS';

    public static function getConfigPeriod(): DBPeriod
    {
        return match (Str::upper(config('cart-scheduler.shift_reservation_duration_period'))) {
            'WEEK', 'WEEKS' => self::Week,
            default => self::Month,
        };
    }
}
