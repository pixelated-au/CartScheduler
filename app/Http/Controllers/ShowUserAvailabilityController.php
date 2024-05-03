<?php

namespace App\Http\Controllers;

use App\Http\Resources\AvailabilityResource;
use App\Http\Resources\UserVacationResource;
use App\Models\UserAvailability;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ShowUserAvailabilityController extends Controller
{
    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(Request $request)
    {
        $settings = app()->make(GeneralSettings::class);
        if (!$settings->enableUserAvailability) {
            throw ValidationException::withMessages(['featureDisabled' => 'Choosing availability is not enabled.']);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->authorize('view', $user);

        $availability = UserAvailability::firstOrCreate(['user_id' => $user->id])->refresh(); // refresh(), otherwise, the we get incomplete data

        return Inertia::render('Profile/ShowAvailability', [
            'vacations'         => UserVacationResource::collection($user->vacations->all()),
            'availability'      => AvailabilityResource::make($availability),
            'selectedLocations' => $user->rosterLocations->pluck('id'),
        ]);
    }
}
