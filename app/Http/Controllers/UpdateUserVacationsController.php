<?php

namespace App\Http\Controllers;

use App\Actions\AdminUpdateUserFunctionalityAction;
use App\Http\Requests\UserVacationRequest;
use App\Models\User;
use App\Models\UserVacation;
use Illuminate\Support\Facades\Redirect;

class UpdateUserVacationsController extends Controller
{
    public function __construct(private readonly AdminUpdateUserFunctionalityAction $adminUpdateUserFunctionalityAction)
    {
    }

    public function __invoke(UserVacationRequest $request)
    {
        /** @var User $user */
        $user      = $request->user();
        $this->authorize('update', $user);

        [$isAdminEdit, $user] = $this->adminUpdateUserFunctionalityAction->execute($request);

        $vacations = $request->validated('vacations', []);

        foreach ($vacations as $vacation) {
            if (isset($vacation['id'])) {
                /** @var \App\Models\UserVacation $userVacation */
                $userVacation = $user->vacations()->find($vacation['id']);
                if (!$userVacation) {
                    continue;
                }
                $userVacation->start_date  = $vacation['start_date'];
                $userVacation->end_date    = $vacation['end_date'];
                $userVacation->description = $vacation['description'];
                $userVacation->save();
            } else {
                $userVacation              = new UserVacation();
                $userVacation->start_date  = $vacation['start_date'];
                $userVacation->end_date    = $vacation['end_date'];
                $userVacation->description = $vacation['description'];
                $userVacation->user_id     = $user->id;
                $userVacation->save();
            }
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
