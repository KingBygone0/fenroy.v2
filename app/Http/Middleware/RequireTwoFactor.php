<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->two_factor_enabled_at) {
            return $next($request);
        }

        // Allow the challenge page itself through (exact route name, not path prefix)
        if ($request->routeIs('filament.admin.pages.two-factor-challenge')) {
            return $next($request);
        }

        if (! session('two_factor_verified')) {
            return redirect('/store-portal/two-factor-challenge');
        }

        return $next($request);
    }
}
