<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $siteName;
    public string $currentVersion;
    public string $availableVersion;
    /** @var int[] */
    public array $allowedSettingsUsers;
    public int $systemShiftStartHour;
    public int $systemShiftEndHour;
    public bool $enableUserAvailability;
    public string $emailReminderTime;
    /** @var bool allow the volunteer to choose which locations they wish to be rostered onto */
    public bool $enableUserLocationChoices;

    public static function group(): string
    {
        return 'general';
    }
}
