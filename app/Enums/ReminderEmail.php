<?php

namespace App\Enums;

enum ReminderEmail : string
{
    case DefaultEmail = 'emails.upcoming-shift';
    case IssueEmail = 'emails.upcoming-shift-issue';
}
