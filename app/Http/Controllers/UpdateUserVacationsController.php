<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UpdateUserVacationsController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('update', $request->user());

        session()->flash('flash.banner', 'Your temporary unavailable dates have been updated.');
        session()->flash('flash.bannerStyle', 'success');

        return Redirect::route('user.availability');
    }
}
