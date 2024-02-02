<?php

namespace App\Actions;

use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Carbon;

class UserNeedsToUpdateAvailability
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    public function execute(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        if (!$this->settings->enableUserAvailability) {
            return false;
        }

        $user->load('availability');
        $availability = $user->availability;

        if (empty($availability)) {
            return true;
        }

        if ($availability->created_at->eq($availability->updated_at)
            && !$availability->num_mondays
            && !$availability->num_tuesdays
            && !$availability->num_wednesdays
            && !$availability->num_thursdays
            && !$availability->num_fridays
            && !$availability->num_saturdays
            && !$availability->num_sundays) {
            return true;
        }

        if ($availability->updated_at->diffInMonths(Carbon::now()) >= 1) {
            return true;
        }

        return false;
    }
}
