<?php

namespace App\Http\Controllers;

use App\Actions\IsAdminForUpdateOfUserAction;
use App\Http\Requests\UserVacationRequest;
use App\Models\User;
use App\Models\UserVacation;
use Illuminate\Support\Facades\Redirect;

class UpdateUserVacationsController extends Controller
{
    public function __construct(private readonly IsAdminForUpdateOfUserAction $isAdminForUpdateOfUserAction)
    {
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(UserVacationRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('update', $user);

        [$isAdminEdit, $user] = $this->isAdminForUpdateOfUserAction->execute($request);

        $vacations = $request->validated('vacations', []);

        foreach ($vacations as $vacation) {
            if (isset($vacation['id'])) {
                $userVacation = $user->vacations()->find($vacation['id']);
                if (!$userVacation) {
                    continue;
                }
            } else {
                $userVacation          = new UserVacation();
                $userVacation->user_id = $user->id;
            }

            $userVacation->start_date  = $vacation['start_date'];
            $userVacation->end_date    = $vacation['end_date'];
            $userVacation->description = $vacation['description'];
            $userVacation->save();
        }

        $deleted = $request->validated('deletedVacations', []);
        foreach ($deleted as $vacation) {
            if (!isset($vacation['id'])) {
                continue;
            }
            /** @var \App\Models\UserVacation $userVacation */
            $userVacation = $user->vacations()->find($vacation['id']);
            if (!$userVacation) {
                continue;
            }
            $userVacation->delete();
        }

        session()->flash('flash.banner', $isAdminEdit ? 'Volunteer holidays have been updated.' : 'Your holidays have been updated.');
        session()->flash('flash.bannerStyle', 'success');

        if ($isAdminEdit) {
            return Redirect::route('admin.users.edit', $user);
        }
        return Redirect::route('user.availability');
    }
}
