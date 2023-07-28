<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminRunSoftwareUpdateController extends Controller
{
    public function __invoke(Request $request)
    {
        Artisan::call('cart-scheduler:do-update --force');
        return Artisan::output();
    }
}
