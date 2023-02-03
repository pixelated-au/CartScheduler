<?php

return [
    'shift_reservation_duration'        => env('CA_SHIFT_RESERVATION_DURATION', 1),
    'shift_reservation_duration_period' => env('CA_SHIFT_RESERVATION_DURATION_PERIOD', 'MONTH'),
    'release_weekly_shifts_on_day'      => env('CA_SHIFT_RESERVATION_WEEK_RELEASE_DAY', 1),
    'do_release_shifts_daily'           => env('CA_SHIFT_RESERVATION_DAILY_RELEASE_SCHEDULE', false),
];
