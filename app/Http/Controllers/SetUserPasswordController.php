<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SetUserPasswordController extends Controller
{
    public function show(User $user, string $hashedEmail): Response|RedirectResponse
    {
        if (Auth::user()) {
            return Redirect::route('dashboard');
        }

        if ($user->password) {
            return Redirect::route('login');
        }

        if (!Hash::check($user->uuid . $user->email, base64_decode($hashedEmail))) {
            abort(404);
        }

        return Inertia::render('Profile/SetPassword', [
            'editUser'    => $user,
            'hashedEmail' => $hashedEmail,
            'siteName'    => config('app.name'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $this->validate($request, [
            'password'     => ['required', 'confirmed'],
            'hashed_email' => ['required', 'string'],
            'user_id'      => ['required', 'integer'],
        ]);
        $user = User::findOrFail($data['user_id']);

        if (!Hash::check($user->uuid . $user->email, base64_decode($data['hashed_email']))) {
            abort(404);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        session()->flash('flash.setPassword', "Your password has been set. Please use it to log in.");

        return Redirect::route('login');
    }
}
