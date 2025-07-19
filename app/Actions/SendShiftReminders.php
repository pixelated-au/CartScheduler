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
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;

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
            Log::info("current date: ", [ "dat " => $this->date ]);
        } else {
            $targetDate = Carbon::today();
            $hours      = $settings->emailReminderTime;
            $targetDate->modify("+$hours hours");
            Log::info("new date: ", [ "dat " => $targetDate ]);
        }

        $users = $getUserShiftsData->execute($targetDate, $this->userId);

        /** @var User $user */
        foreach ($users as $user) {
            $emailTemplate = GetEmailReminderTemplate::GetReminderTemplate()->content;
            $problemShifts = collect([]);
            foreach ($user->shifts as $userShift) {
                $targetShiftId = $userShift->pivot->shift_id;
                $targetShiftDate = $userShift->pivot->shift_date;

                Log::info("target shift date: ", [ "dat" => $targetShiftDate ]);

                // This function: needs to be re looked at it - it seems to find shifts for +2 days, whereas the above SQL query gets all users for +3 days
                $targetShiftUsers = $users->filter(function ($shiftUser) use ($targetShiftId, $targetShiftDate) {
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
                    //$emailTemplate = ReminderEmail::IssueEmail;
                }
            }

            $relativeDate = match ((int) Carbon::now()->startOfDay()->diffInDays($targetDate)) {
                0 => 'today',
                1 => 'tomorrow',
                2, 3, 4, 5, 6 => ($targetDate->isNextWeek() ? 'next ' : 'this ') . $targetDate->getTranslatedDayName(),
                default => $targetDate->startOfDay()->from(
                    other: Carbon::now()->startOfDay(),
                    syntax: CarbonInterface::DIFF_RELATIVE_TO_NOW,
                    options: Carbon::JUST_NOW | Carbon::ONE_DAY_WORDS | Carbon::TWO_DAY_WORDS | Carbon::SEQUENTIAL_PARTS_ONLY
                ),
            };

            $emailTemplate = preg_replace('/\{\{\s*app_name\s*\}\}/', config('app.name'), $emailTemplate);
            $emailTemplate = preg_replace('/\{\{\s*user_name\s*\}\}/', $user->name, $emailTemplate);
            $emailTemplate = preg_replace('/\{\{\s*relative_date\s*\}\}/', $relativeDate, $emailTemplate);
            $emailTemplate = preg_replace('/\{\{\s*full_date\s*\}\}/', $targetDate->format('l, F jS'), $emailTemplate);

            $shiftInfoList = "";
            foreach ($user->shifts as $shift) {
                $shiftInfoList = $shiftInfoList . "- {$shift->location->name} from {$shift->start_time12_hr} to {$shift->end_time12_hr}\n";
            }

            $problemShiftInfoList = "";
            foreach ($problemShifts as $shift) {
                $problemShiftInfoList = $problemShiftInfoList . "- {$shift->location->name} from {$shift->start_time12_hr} to {$shift->end_time12_hr}\n";
            }

            $resultShiftInfo = "";
            if ($shiftInfoList) {
                $resultShiftInfo = "**Upcoming shift(s):**\n" . $shiftInfoList;
            }
            if ($problemShiftInfoList) {
                $resultShiftInfo = $resultShiftInfo . "*The following shifts are currently unable to run:*\n" . $problemShiftInfoList;
            }

            $emailTemplate = preg_replace('/\{\{\s*shift_info\s*\}\}/', $resultShiftInfo, $emailTemplate);

            Log::debug("template: ", [ "template" => $emailTemplate ]);

            Mail::to($user->email)->send(new ShiftReminder(
                body: $emailTemplate,
            ));
        }
    }
}

