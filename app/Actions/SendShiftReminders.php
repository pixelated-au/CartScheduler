<?php

namespace App\Actions;

use App\Actions\GetUserShiftReminderData;
use Illuminate\Support\Facades\Mail;
use App\Mail\ShiftReminder;
use App\Models\Shift;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Support\Collection;
use \Datetime;


class SendShiftReminders
{
    public function __construct(
        private readonly GeneralSettings $settings,
        private readonly GetUserShiftReminderData $getUserShiftsData,
    ) {}

    public function __invoke()
    {
        //$getUserShiftsData = GetUserShiftsData;
        error_log("calling hourly question.");
        info("calling hourly question.");

        $targetDate = new DateTime("today");
        info("dattime.");
        info("got settings.");
        $days = $this->settings->emailReminderTime;
        info("assigned $days settings.");
        $targetDate->modify("+$days day"); //e.g. "+1 day"
        info("assembled date $targetDate.");

        info("got data.");
        $users = $this->getUserShiftsData->execute($targetDate->format("Y-m-d"));
        info("executing.");
        $tmpDate = $targetDate->format("Y-m-d");
        error_log("targetDate:  $tmpDate");
        error_log("received data:  $users");

        $users = $this->aggregateUserData($users);

        foreach ($users as $user) {
            //var_dump($user);
            // $email = $user['user_email'];
            // $name = $user['user_name'];
            // $gender = $user['user_gender'];
            // $shifts = $user['shifts'];

            // $dayString = $targetDate->format("jS") . " of " . $targetDate->format("F");

            // There's probably not a need to resolve this with the container as Mail can be mocked for testing
            Mail::to($user['user_email'])->queue(new ShiftReminder(
                date: $targetDate->format('l, F j'),
                name: $user['user_name'],
                gender: $user['user_gender'],
                shifts: $user['shifts'],
            ));
            // Mail::to($email)->queue(app()->makeWith(ShiftReminder::class, ["date" => $dayString, "name" => $name, "gender" => $gender, "shifts" => $shifts]));
        }
    }

    private static function aggregateUserData(Collection $userData)
    {
        return $userData
            ->map(function (User $user) {
                $shifts = [];
                $user['shifts']->map(function (Shift $shift) use ($shifts) { // This may need to be use (&$shifts)
                    // Not entirly sure what this is doing, I feel like it could be simplified, but I'll check it out when you do a pull request 👍
                    $shift_data = explode("|", $shift);
                    $shift_data[1] = date("g:ia", strtotime($shift_data[1]));
                    array_push($shifts, $shift_data);
                });

                return [
                    "user_id" => $user['user_id'],
                    "user_email" => $user['user_email'],
                    "user_name" => $user['user_name'],
                    "user_gender" => $user['user_gender'],
                    "shifts" => $shifts,
                ];
            });

        // $finalUserData = new Collection();
        // foreach ($userData as $user) {
        //     //var_dump($user);

        //     $shifts = [];
        //     foreach ($user['shifts'] as $shift) {
        //         //convert the string into an actual array of vals we can use
        //         $shift_data = explode("|", $shift);
        //         $shift_data[1] = date("g:ia", strtotime($shift_data[1]));
        //         array_push($shifts, $shift_data);
        //     }

        //     $reconstructedData = [
        //         "user_id" => $user['user_id'],
        //         "user_email" => $user['user_email'],
        //         "user_name" => $user['user_name'],
        //         "user_gender" => $user['user_gender'],
        //         "shifts" => $shifts
        //     ];

        //     $finalUserData->push($reconstructedData);
        // }

        // return $finalUserData;
    }
}
