<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    public function delete(mixed $user): void
    {
        DB::transaction(static function () use ($user) {
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }
}
