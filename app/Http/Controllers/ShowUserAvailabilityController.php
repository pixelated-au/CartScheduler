<?php

namespace App\Http\Controllers;

use App\Http\Resources\AvailabilityResource;
use App\Http\Resources\UserVacationResource;
use App\Models\UserAvailability;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShowUserAvailabilityController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->authorize('view', $user);

        $availability = UserAvailability::firstOr(fn() => UserAvailability::create(['user_id' => $user->id]));

        return Inertia::render('Profile/ShowAvailability', [
            'vacations' => UserVacationResource::collection($user->vacations->all()),
            'availability' => AvailabilityResource::make($availability),
        ]);
    }
}
