<?php

namespace App\Http\Controllers;

use App\Actions\AdminUpdateUserFunctionalityAction;
use App\Http\Requests\UserLocationChoicesRequest;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;

class UpdateUserLocationsChoicesController extends Controller
{
    public function __construct(private readonly AdminUpdateUserFunctionalityAction $adminUpdateUserFunctionalityAction)
    {
    }

    public function __invoke(UserLocationChoicesRequest $request)
    {
        /** @var user $user */
        $user = $request->user();
        $this->authorize('update', $user);

        [$isAdminEdit, $user] = $this->adminUpdateUserFunctionalityAction->execute($request);

        $locationIds = $request->validated('selectedLocations', []);

        $user->rosterLocations()->sync($locationIds);

        session()->flash('flash.banner', $isAdminEdit ? 'volunteer holidays have been updated.' : 'your holidays have been updated.');
        session()->flash('flash.bannerstyle', 'success');

        if ($isAdminEdit) {
            return redirect::route('admin.users.edit', $user);
        }
        return redirect::route('user.availability');
    }
}
