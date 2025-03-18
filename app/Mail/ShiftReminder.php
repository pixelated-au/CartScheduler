<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Enums\ReminderEmail;

class ShiftReminder extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public Carbon $date,
        public string $name,
        public string $gender,
        public Collection $shifts,
        public Collection $problemShifts,
        public ReminderEmail $emailTemplate,
    ) {
        $this->subject = config('app.name') . ' Upcoming ' . Str::plural('Shift', $shifts->count());
    }

    public function content(): Content
    {
        return new Content(
            markdown: $this->emailTemplate->value,
            with: [
                'relativeDate' => match ((int) Carbon::now()->startOfDay()->diffInDays($this->date)) {
                    0 => 'today',
                    1 => 'tomorrow',
                    2, 3, 4, 5, 6 => ($this->date->isNextWeek() ? 'next ' : 'this ') . $this->date->getTranslatedDayName(),
                    default => $this->date->startOfDay()->from(
                        other: Carbon::now()->startOfDay(),
                        syntax: CarbonInterface::DIFF_RELATIVE_TO_NOW,
                        options: Carbon::JUST_NOW | Carbon::ONE_DAY_WORDS | Carbon::TWO_DAY_WORDS | Carbon::SEQUENTIAL_PARTS_ONLY
                    ),
                },
            ],
        );
    }
}
