<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Actions\SendShiftReminders;
use Illuminate\Support\Facades\App;
use Illuminate\Console\Scheduling\CallbackEvent;

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


Schedule::call(SendShiftReminders::class)
    ->tap(fn (CallbackEvent $event) =>
        App::isLocal()
            ? $event->everyThirtySeconds()
            : $event->daily())
    ->everyMinute();

