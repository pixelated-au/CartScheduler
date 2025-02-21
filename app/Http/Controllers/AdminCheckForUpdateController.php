<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Pixelated\Streamline\Actions\CheckAvailableVersions;

class AdminCheckForUpdateController extends Controller
{
    public function __construct(
        private readonly GeneralSettings $settings,
        private readonly CheckAvailableVersions $availableVersions,
    ) {
    }

    public function __invoke(Request $request)
    {
        $old  = config('streamline.installed_version');
        // Ensure the general settings current version is up to date,
        if ($old && $this->settings->currentVersion && version_compare($old, $this->settings->currentVersion, '<')) {
            $this->settings->currentVersion = $old;
        }

        $new = $this->availableVersions->execute(ignorePreReleases: $request->boolean('beta'));
        $this->settings->availableVersion = $new;
        $this->settings->save();

        return version_compare($old, $new, '!=');
    }
}
