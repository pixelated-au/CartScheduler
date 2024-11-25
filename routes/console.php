<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
//use Illuminate\Console\Scheduling\Schedule;
use App\Actions\GetUserShiftReminderData;
use Illuminate\Support\Facades\Mail;
use App\Mail\ShiftReminder;
use Illuminate\Support\Collection;

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


Schedule::call(function () {
    //$getUserShiftsData = GetUserShiftsData;
    error_log("calling hourly question.");
    info("calling hourly question.");
    $target = date('Y-m-d', time());
    $getUserShiftsData = new GetUserShiftReminderData();
    $users = $getUserShiftsData->execute($target);
    error_log("received data:  $users");
    info("received data:  $users");
    foreach ($users as $user) {
        var_dump($user);
        $id = $user['user_id'];
        $email = $user['user_email'];
        $name = $user['user_name'];
        $gender = $user['user_gender'];

        Mail::to($email)->send(new ShiftReminder($target, $name, $gender));

    }
})->everyMinute();

