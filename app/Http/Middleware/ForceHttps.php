<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && config('app.is_https')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
