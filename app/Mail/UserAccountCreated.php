<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class UserAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public User $user)
    {
        $this->subject = config('app.name') . ' Account Activation';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $hashedEmail = base64_encode(Hash::make($this->user->uuid . $this->user->email));

        return $this->markdown('emails.user-account-created')->with('hashedEmail', $hashedEmail);
    }
}
