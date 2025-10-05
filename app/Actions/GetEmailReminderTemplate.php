<?php

namespace App\Actions;

use App\Models\EmailTemplate;

class GetEmailReminderTemplate
{
    public static function GetReminderTemplate() : object
    {
        return EmailTemplate::where("name", "reminder_email")->first();
    }
}
