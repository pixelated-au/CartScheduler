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
     * @param \App\Http\Requests\UserVacationRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function __invoke(UserVacationRequest $request)
    {
        /** @var User $user */
        [$isAdminEdit, $user] = $this->isAdminForUpdateOfUserAction->execute($request);

        $vacations = $request->validated('vacations', []);

        foreach ($vacations as $vacation) {
            if (isset($vacation['id'])) {
                $userVacation = $user->vacations()->find($vacation['id']);
            } else {
                // If there is no id, create a new vacation
                $userVacation          = new UserVacation();
                $userVacation->user_id = $user->getKey();
            }

            $userVacation->start_date  = $vacation['start_date'];
            $userVacation->end_date    = $vacation['end_date'];
            $userVacation->description = $vacation['description'];
            $userVacation->save();
        }

        $toBeDeleted = $request->validated('deletedVacations', []);
        foreach ($toBeDeleted as $toDelete) {
            $userVacation = $user->vacations()->find($toDelete['id']);
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
