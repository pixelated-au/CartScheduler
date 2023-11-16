<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminCheckForUpdateController extends Controller
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    public function __invoke(Request $request)
    {
        $old = $this->settings->availableVersion;
        $params = [];
        $beta = $request->boolean('beta');
        if ($beta) {
            $params['--beta'] = true;
        }
        Artisan::call('cart-scheduler:has-update', $params);
        $new = $this->settings->availableVersion;
        return version_compare($old, $new, '!=');
    }
}
