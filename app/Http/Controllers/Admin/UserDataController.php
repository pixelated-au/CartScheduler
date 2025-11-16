<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Actions\GetAvailableShiftsCount;
use App\Actions\GetLocationsForShifts;
use App\Data\LocationData;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;


class UserDataController extends Controller
{
    public function __construct() {}

    public function __invoke(Request $request, string $shiftDate)
    {
        return $shiftDate;
    }
}
