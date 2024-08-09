<?php

namespace App\Actions;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class CheckHashedEmailAddress
{
    public function execute(User $user, $hashedEmail) : bool
    {
        try {
            if (!Hash::check($user->uuid . $user->email, base64_decode((string) $hashedEmail))) {
                return false;
            }
        } catch (Exception $e) {
            report($e);
            return false;
        }
        return true;
    }
}
