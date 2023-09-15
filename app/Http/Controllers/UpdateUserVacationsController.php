<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserVacationRequest;
use App\Models\User;
use App\Models\UserVacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UpdateUserVacationsController extends Controller
{
    public function __invoke(UserVacationRequest $request)
    {
        $this->authorize('update', $request->user());

        /** @var User $user */
        $user = $request->user();
        $vacations = $request->validated('vacations');

        foreach ($vacations as $vacation) {
            if (isset($vacation['id'])) {
                /** @var \App\Models\UserVacation $userVacation */
                $userVacation = $user->vacations()->find($vacation['id']);
                $userVacation->start_date = $vacation['start_date'];
                $userVacation->end_date = $vacation['end_date'];
                $userVacation->description = $vacation['description'];
                $userVacation->save();
            } else {
                $userVacation = new UserVacation();
                $userVacation->start_date = $vacation['start_date'];
                $userVacation->end_date = $vacation['end_date'];
                $userVacation->description = $vacation['description'];
                $userVacation->user_id = $user->id;
                $userVacation->save();
            }
        }


        session()->flash('flash.banner', 'Your availability has been updated.');
        session()->flash('flash.bannerStyle', 'success');

        return Redirect::route('user.availability');
    }
}
