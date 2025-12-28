<?php

namespace App\Enums;

enum ShiftsPerDay: string
{
    case OneShift = '1 shift';
    case TwoShiftsBackToBack = '2 shifts back-to-back';
    case TwoShiftsBreakInBetween = '2 shifts break-in-between';
}
