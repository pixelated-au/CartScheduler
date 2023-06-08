<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UpdateUserRegularAvailabilityController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('update', $request->user());

        session()->flash('flash.banner', 'Your availability has been updated.');
        session()->flash('flash.bannerStyle', 'success');

        return Redirect::route('user.availability');
    }
}
