<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        return Inertia::render('Admin/Users/List', [
            'users' => User::all(),
        ]);
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function show($id)
    {
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'editUser' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return Redirect::route('admin.users.edit', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        $name = $user->name;
        $user->delete();

        session()->flash('flash.banner', "User $name successfully deleted.");
        session()->flash('flash.bannerStyle', 'danger');

        return Redirect::route('admin.users.index');
    }
}
