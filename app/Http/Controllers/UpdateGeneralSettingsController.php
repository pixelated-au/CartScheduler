<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Settings\GeneralSettings;

class UpdateGeneralSettingsController extends Controller
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    public function __invoke(UpdateGeneralSettingsRequest $request)
    {
        $this->settings->siteName                  = $request->input('siteName');
        $this->settings->systemShiftStartHour      = $request->input('systemShiftStartHour');
        $this->settings->systemShiftEndHour        = $request->input('systemShiftEndHour');
        $this->settings->enableUserAvailability    = $request->input('enableUserAvailability');
        $this->settings->enableUserLocationChoices = $request->input('enableUserLocationChoices');
        $this->settings->save();

        return to_route('admin.settings');
    }
}
