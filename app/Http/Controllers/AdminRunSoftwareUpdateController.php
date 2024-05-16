<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class AdminRunSoftwareUpdateController extends Controller
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }


    public function __invoke()
    {
        $available = $this->settings->availableVersion;
        $params = ['--force' => true];
        if (Str::endsWith($available, 'b')) {
            $params['--beta'] = true;
        }

        Artisan::call('cart-scheduler:do-update', $params);
        return Artisan::output();
    }
}
