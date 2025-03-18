<?php

namespace App\Actions;

use App\Mail\ShiftReminder;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Enums\ReminderEmail;

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

        $getuser = new GetUserShiftsData();


        /** @var User $user */
        foreach ($users as $user) {
            $emailTemplate = ReminderEmail::DefaultEmail;
            $problemShifts = collect([]);
            foreach ($user->shifts as $userShift) {
                $targetShiftId = $userShift->pivot->shift_id;
                $targetShiftDate = $userShift->pivot->shift_date;

                $targetShiftUsers = $users->where(function ($shiftUser) use ($targetShiftId, $targetShiftDate) {
                    foreach ($shiftUser->shifts as $shift) {
                        if ($shift->pivot->shift_id == $targetShiftId && $shift->pivot->shift_date == $targetShiftDate) {
                            return true;
                        }
                    }
                    return false;
                });

                $shiftCanRun = CanShiftRun::CanShiftRun($userShift->location_id, $targetShiftUsers);

                if (!$shiftCanRun) {
                    $problemShifts->push($userShift);
                    $emailTemplate = ReminderEmail::IssueEmail;
                }
            }

            Mail::to($user->email)->send(new ShiftReminder(
                date: $targetDate,
                name: $user->name,
                gender: $user->gender,
                shifts: $user->shifts,
                problemShifts: $problemShifts,
                emailTemplate: $emailTemplate,
            ));
        }
    }
}

