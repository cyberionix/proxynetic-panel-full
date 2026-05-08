<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckKyc
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        // first_checkout_bypass: allow user with no orders to access payment + profile setup routes
        if ($user && $user->orders()->count() === 0) {
            if ($request->routeIs(...['portal.basket.*', 'portal.checkout', 'portal.nestpayCheckout', 'portal.paymentWithBalance', 'portal.paytr.*', 'portal.users.profile', 'portal.users.addresses.*', 'portal.users.savePhoneAndSendVerificationOTP', 'portal.users.storeIdentityNumber', 'portal.users.checkKyc', 'portal.users.verifyEmail', 'portal.auth.logout', 'portal.auth.send_email_otp', 'portal.auth.verify_email_otp', 'portal.auth.send_phone_otp', 'portal.auth.verify_phone_otp', 'portal.auth.update_email', 'portal.auth.update_phone', 'portal.dashboard', 'portal.dashboard.getData', 'portal.district.search', 'portal.eftIframeToken', 'portal.save_bank_transfer_notification'])) {
                return $next($request);
            }
        }


        if ($user && $user->is_force_kyc && !$user->kyc?->verified_at) {
            if (!$request->routeIs(['portal.dashboard', 'portal.users.storeIdentityNumber', 'portal.users.checkKyc', 'portal.users.savePhoneAndSendVerificationOTP'])) {
                return redirect()->route('portal.dashboard');
            }
        }

        return $next($request);
    }
}
