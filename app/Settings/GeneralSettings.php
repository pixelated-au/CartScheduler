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

    public static function group(): string
    {
        return 'general';
    }
}
