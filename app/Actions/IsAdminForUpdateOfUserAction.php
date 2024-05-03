<?php

namespace App\Actions;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class IsAdminForUpdateOfUserAction
{
    /**
     * @param \Illuminate\Foundation\Http\FormRequest $request - Expects an option 'user_id' to be set if admin is editing another user's availability.
     * @return array{isAdminEdit: bool, user: User} Returns an associative array with 'isAdminEdit' indicating if admin is editing the user, and 'user' as the User instance.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function execute(FormRequest $request): array
    {
        $isAdminEdit = false;
        if ($request->has('user_id')) {
            abort_if($request->user()->role !== Role::Admin->value, Response::HTTP_UNAUTHORIZED, 'Unauthorized');

            // this means admin is updating another user's availability
            $isAdminEdit = true;
            $user        = User::findOrFail($request->validated('user_id'));
        } else {
            $user = $request->user();
        }

        return [$isAdminEdit, $user];
    }
}
