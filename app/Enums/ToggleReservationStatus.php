<?php

namespace App\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum ToggleReservationStatus
{
    case RESERVATION_MADE;
    case RESERVATION_REMOVED;
    case NO_AVAILABLE_SHIFTS;
}
