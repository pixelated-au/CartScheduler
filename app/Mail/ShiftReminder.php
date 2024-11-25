<?php

namespace App\Mail;

//use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Support\Facades\Hash;

class ShiftReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public string $date, public string $name, public string $gender) //public User $user,
    {
        $this->date = $date;
        $this->name = $name;
        $this->gender = $gender;

        $this->subject = config('app.name') . ' Upcoming Shift';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        //$hashedEmail = base64_encode(Hash::make($this->user->uuid . $this->user->email));
        //$data['date'] = $this->date;
        //$data['name'] = $this->name;

        return $this->markdown('emails.upcoming-shift');
    }
}
