<?php

namespace App\Http\Controllers;

use App\Actions\CheckHashedEmailAddress;
use App\Interfaces\ObfuscatedErrorCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SetUserPasswordController extends Controller
{
    public function __construct(private readonly ObfuscatedErrorCode $errorCodeAction, private readonly CheckHashedEmailAddress $checkHashedEmailAddress)
    {
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show(User $user, string $hashedEmail): Response|RedirectResponse
    {
        if (Auth::user()) {
            return Redirect::route('dashboard');
        }

        if ($user->password) {
            return Redirect::route('login');
        }

        abort_unless($this->checkHashedEmailAddress->execute($user, $hashedEmail), SymfonyResponse::HTTP_NOT_FOUND);

        return Inertia::render('Profile/SetPassword', [
            'editUser'    => ['id' => $user->id, 'name' => $user->name],
            'hashedEmail' => $hashedEmail,
            'siteName'    => config('app.name'),
        ]);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $this->validate($request, [
            'password_confirmation' => ['required'],
            'password'              => ['required', 'confirmed'],
            'hashed_email'          => ['required', 'string'],
            'user_id'               => ['required', 'integer', 'exists:users,id'],
        ],
            [
                'hashed_email.required' => $this->errorCodeAction->errorCode(100),
                'hashed_email.string'   => $this->errorCodeAction->errorCode(200),
                'user_id.required'      => $this->errorCodeAction->errorCode(300),
                'user_id.integer'       => $this->errorCodeAction->errorCode(400),
                'user_id.exists'        => $this->errorCodeAction->errorCode(500),
            ]);
        $user = User::findOrFail($data['user_id']);

        abort_unless($this->checkHashedEmailAddress->execute($user, $data['hashed_email']), SymfonyResponse::HTTP_NOT_FOUND);


//        if (!Hash::check($user->uuid . $user->email, base64_decode($data['hashed_email']))) {
//            abort(404);
//        }
        $user->update([
            'password' => Hash::make($data['password']),
        ]);
        session()->flash('flash.setPassword', "Your password has been set. Please use it to log in.");
        return Redirect::route('login');
    }
}
