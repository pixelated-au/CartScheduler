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
        $this->authorize('view', $request->user());

        return Inertia::render('Profile/ShowAvailability', [
//            'exampleFile' => asset('storage/example-user-import.xlsx'),
        ]);
    }
}
