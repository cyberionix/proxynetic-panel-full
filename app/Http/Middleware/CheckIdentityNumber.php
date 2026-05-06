<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIdentityNumber
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && empty($user->identity_number_verified_at)) {
            if (!$request->routeIs(['portal.dashboard','portal.district.search','portal.users.addresses.store', 'portal.users.storeIdentityNumber', 'portal.users.checkKyc', 'portal.users.savePhoneAndSendVerificationOTP'])  ) {
                return redirect()->route('portal.dashboard');
            }
        }

        return $next($request);
    }
}
