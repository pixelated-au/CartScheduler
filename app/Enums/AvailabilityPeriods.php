<?php

namespace App\Enums;

enum AvailabilityPeriods: string
{
    case Morning      = 'morning';
    case Afternoon    = 'afternoon';
    case Evening      = 'evening';
    case FullDay      = 'full-day';
    case HalfDay      = 'half-day';
    case NotAvailable = 'not-available';

    public static function values(): array
    {
        return array_map(fn(self $dayPart) => $dayPart->value, self::cases());
    }
}
