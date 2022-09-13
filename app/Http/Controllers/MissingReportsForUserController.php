<?php

namespace App\Http\Controllers;

use App\Actions\GetOutstandingReports;
use Illuminate\Http\Request;

class MissingReportsForUserController extends Controller
{
    public function __invoke(Request $request, GetOutstandingReports $getOutstandingReports)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return $getOutstandingReports->execute($user)
                                     ->filter(function ($val) use ($user) {
                                         if ($val->requires_brother) {
                                             return $user->gender === 'male';
                                         }

                                         return true;
                                     });
    }
}
