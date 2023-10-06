<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAvailabilityRequest;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;

class UpdateUserRegularAvailabilityController extends Controller
{
    public function __invoke(UserAvailabilityRequest $request)
    {
        $user = $request->user();
        $this->authorize('update', $user);

        $isOther = false;
        if ($request->validated('user_id')) {
            // this means admin is updating another user's availability
            $isOther = true;
            $user = User::findOrFail($request->validated('user_id'));
        }

        $user->availability->num_mondays    = $request->validated('num_mondays');
        $user->availability->num_tuesdays   = $request->validated('num_tuesdays');
        $user->availability->num_wednesdays = $request->validated('num_wednesdays');
        $user->availability->num_thursdays  = $request->validated('num_thursdays');
        $user->availability->num_fridays    = $request->validated('num_fridays');
        $user->availability->num_saturdays  = $request->validated('num_saturdays');
        $user->availability->num_sundays    = $request->validated('num_sundays');

        $user->availability->day_monday    = $request->validated('day_monday');
        $user->availability->day_tuesday   = $request->validated('day_tuesday');
        $user->availability->day_wednesday = $request->validated('day_wednesday');
        $user->availability->day_thursday  = $request->validated('day_thursday');
        $user->availability->day_friday    = $request->validated('day_friday');
        $user->availability->day_saturday  = $request->validated('day_saturday');
        $user->availability->day_sunday    = $request->validated('day_sunday');

        $user->availability->comments = $request->validated('comments');

        $user->availability->save();

        session()->flash('flash.banner', $isOther ? 'Volunteer availability has been updated.' : 'Your availability has been updated.');
        session()->flash('flash.bannerStyle', 'success');

        if ($isOther) {
            return Redirect::route('admin.users.edit', $user);
        }
        return Redirect::route('user.availability');
    }
}
