<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLocationChoicesRequest;
use App\Models\User;
use App\Models\UserVacation;
use Illuminate\Support\Facades\Redirect;

class UpdateUserLocationsChoicesController extends Controller
{
    public function __invoke(UserLocationChoicesRequest $request)
    {
        /** @var user $user */
        $user = $request->user();
        $this->authorize('update', $user);

        $isother = false;
        if ($request->validated('user_id')) {
            // this means admin is updating another user's availability
            $isother = true;
            $user    = user::findorfail($request->validated('user_id'));
        }
        ray('here');

        $locationIds = $request->validated('selectedLocations', []);

        ray($locationIds);

        $user->rosterLocations()->sync($locationIds);

        session()->flash('flash.banner', $isother ? 'volunteer holidays have been updated.' : 'your holidays have been updated.');
        session()->flash('flash.bannerstyle', 'success');

        if ($isother) {
            return redirect::route('admin.users.edit', $user);
        }
        return redirect::route('user.availability');
    }
}
