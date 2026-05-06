<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckVerifiedPhone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && empty($user->phone_verified_at)) {
            if (!$request->routeIs(['portal.dashboard', 'portal.users.savePhoneAndSendVerificationOTP'])  ) {
                return redirect()->route('portal.dashboard');
            }
        }

        return $next($request);
    }
}
