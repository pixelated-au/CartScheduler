<?php

namespace App\Enums;

enum WeekdayAvailability: string
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Evening = 'evening';
    case FullDay = 'full-day';
    case HalfDay = 'half-day';
    case NotAvailable = 'not-available';
}
