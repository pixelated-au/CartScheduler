<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAllowedSettingsUsersRequest;
use App\Models\Location;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class AdminCheckForUpdateController extends Controller
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    public function __invoke()
    {
        $old = $this->settings->availableVersion;
        Artisan::call('cart-scheduler:has-update');
        $new = $this->settings->availableVersion;
        return version_compare($old, $new, '!=');
    }
}
