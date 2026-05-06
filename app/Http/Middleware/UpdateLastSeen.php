<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ignoreRoute = [
            "portal.invoices.ajax",
            "portal.users.addresses.find",
            "portal.district.search"
        ];

        if (Auth::guard('web')->check() && Str::before($request->route()->getName(), '.') == "portal" && !in_array($request->route()->getName(), $ignoreRoute)) {
            Auth::guard('web')->user()->update(['last_seen_at' => now()]);
        }
        return $next($request);
    }
}
