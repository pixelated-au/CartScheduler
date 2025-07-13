<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class ShiftReminder extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public string $body,
    ) {
        $this->subject = config('app.name') . ' Upcoming Shift(s)';
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.upcoming-shift",
        );
    }
}

