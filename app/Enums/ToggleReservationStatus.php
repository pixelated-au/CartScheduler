<?php

namespace App\Enums;

enum ToggleReservationStatus
{
    case RESERVATION_MADE;
    case RESERVATION_REMOVED;
    case NO_AVAILABLE_SHIFTS;
}
