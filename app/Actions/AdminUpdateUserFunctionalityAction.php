<?php

namespace App\Actions;

use App\Http\Controllers\UpdateUserLocationsChoicesController;
use App\Http\Requests\UserLocationChoicesRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Redirect;

class AdminUpdateUserFunctionalityAction
{
    /**
     * @param \Illuminate\Foundation\Http\FormRequest $request - Expects an option 'user_id' to be set if admin is editing another user's availability.
     * @return array{isAdminEdit: bool, user: User} Returns an associative array with 'isAdminEdit' indicating if admin is editing the user, and 'user' as the User instance.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function execute(FormRequest $request): array
    {
        $isAdminEdit = false;
        if ($request->validated('user_id')) {
            // this means admin is updating another user's availability
            $isAdminEdit = true;
            $user        = User::findorfail($request->validated('user_id'));
        } else {
            $user = $request->user();
        }

        return [$isAdminEdit, $user];
    }
}
