<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsUserEnabledMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->is_enabled) {
            $guards = array_keys(config('auth.guards'));

            foreach ($guards as $guard) {
                $guard = Auth::guard($guard);

                if ($guard instanceof SessionGuard) {
                    $guard->logout();
                }
            }
            return response()->redirectTo('/');
        }
        return $next($request);
    }
}
