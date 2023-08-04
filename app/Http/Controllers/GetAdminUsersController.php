<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminUserResource;
use App\Models\User;
use Illuminate\Http\Request;

class GetAdminUsersController extends Controller
{
    public function __invoke(Request $request)
    {
        $adminUsers = User::query()
            ->select(['id', 'name'])
            ->where('role', 'admin')
            ->where('is_enabled', true)
            ->get();
        return AdminUserResource::collection($adminUsers);
    }
}
