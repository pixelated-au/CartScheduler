<?php

namespace App\Mail;

//use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

//use Illuminate\Support\Facades\Hash;

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
        public Collection $shifts
    ) {
        $this->subject = config('app.name') . ' Upcoming ' . Str::plural('Shift', $shifts->count());
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.upcoming-shift',
            with: [
                'date'    => $this->date,
                'name'    => $this->name,
                'gender'  => $this->gender,
                'shiftss' => $this->shifts,
            ],
        );
    }
}
