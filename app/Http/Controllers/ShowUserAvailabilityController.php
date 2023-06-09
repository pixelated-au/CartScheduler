<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ShowUserAvailabilityController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->authorize('view', $user);

        $user->load('availability');
        //ray($user->availability()->first());
        // Note, availability can be null

        return Inertia::render('Profile/ShowAvailability', [
//            'availability' => $user->availability,
        ]);
    }
}
