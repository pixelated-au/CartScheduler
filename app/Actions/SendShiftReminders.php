<?php

namespace App\Actions;

use App\Actions\GetUserShiftReminderData;
use Illuminate\Support\Facades\Mail;
use App\Mail\ShiftReminder;
use Illuminate\Support\Collection;
use \Datetime;


class SendShiftReminders
{

    public function __invoke()
    {

        //$getUserShiftsData = GetUserShiftsData;
        error_log("calling hourly question.");
        info("calling hourly question.");

        $targetDate = new DateTime("today");
        $targetDate->modify("+1 day");

        $getUserShiftsData = new GetUserShiftReminderData();
        $users = $getUserShiftsData->execute($targetDate->format("Y-m-d"));
        $tmpDate = $targetDate->format("Y-m-d");
        error_log("targetDate:  $tmpDate");
        error_log("received data:  $users");

        $users = $this->aggregateUserData($users);

        foreach ($users as $user) {
            //var_dump($user);
            $email = $user['user_email'];
            $name = $user['user_name'];
            $gender = $user['user_gender'];
            $shifts = $user['shifts'];

            $dayString = $targetDate->format("jS") . " of " . $targetDate->format("F");

            Mail::to($email)->queue(app()->makeWith(ShiftReminder::class, ["date" => $dayString, "name" => $name, "gender" => $gender, "shifts" => $shifts]));


        }

    }

    private static function aggregateUserData(Collection $userData) {
        $finalUserData = new Collection();
        foreach ($userData as $user) {
            //var_dump($user);

            $shifts = [];
            foreach ($user['shifts'] as $shift) {
                //convert the string into an actual array of vals we can use
                $shift_data = explode("|", $shift);
                $shift_data[1] = date("g:ia", strtotime($shift_data[1]));
                array_push($shifts, $shift_data);
            }

            $reconstructedData = [
                "user_id" => $user['user_id'],
                "user_email" => $user['user_email'],
                "user_name" => $user['user_name'],
                "user_gender" => $user['user_gender'],
                "shifts" => $shifts
            ];

            $finalUserData->push($reconstructedData);
        }

        return $finalUserData;
    }

}
