<?php

namespace App\Actions;

use App\Mail\ShiftReminder;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendShiftReminders implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ?int $userId,
        private readonly ?Carbon $date,
    ) {
    }

    public function __invoke(GeneralSettings $settings, GetUserShifts $getUserShiftsData): void
    {
        if ($this->date != null) {
            $targetDate = $this->date;
        } else {
            $targetDate = Carbon::today();
            $hours      = $settings->emailReminderTime;
            $targetDate->modify("+$hours hours"); //e.g. "+1 day"
        }

        $users = $getUserShiftsData->execute($targetDate, $this->userId);

        /** @var User $user */
        foreach ($users as $user) {
            Mail::to($user->email)->send(new ShiftReminder(
                date: $targetDate,
                name: $user->name,
                gender: $user->gender,
                shifts: $user->shifts,
            ));
        }
    }
}

