<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\App;
use Illuminate\Console\Scheduling\CallbackEvent;
use App\Actions\SendShiftReminders;
use App\Actions\GetUserShiftReminderData;
use App\Settings\GeneralSettings;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
== OLD METHOD ==
Schedule::call(SendShiftReminders::class)
    ->tap(fn (CallbackEvent $event) =>
        App::isLocal()
            ? $event->everyThirtySeconds()
            : $event->daily())
    ->everyMinute();
*/
Schedule::call(function () {
        $settings = app()->make(GeneralSettings::class);
        dispatch(new SendShiftReminders($settings, new GetUserShiftReminderData()));
    })
    ->tap(fn (CallbackEvent $event) =>
        App::isLocal()
            ? $event->everyThirtySeconds()
            : $event->daily())
    ->everyMinute();
