<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pixelated\Streamline\Actions\CheckAvailableVersions;

class AdminCheckForUpdateController extends Controller
{
    public function __construct(
        private readonly CheckAvailableVersions $availableVersions,
    ) {
    }

    public function __invoke(Request $request)
    {
        $old = config('streamline.installed_version');
        $new = $this->availableVersions->execute(ignorePreReleases: !$request->boolean('beta'));
        return version_compare($old, $new, '<');
    }
}
