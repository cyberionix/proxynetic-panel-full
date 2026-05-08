<?php

namespace App\Listeners;

use App\Events\CheckoutConfirmed;
use Illuminate\Support\Facades\Log;

class TriggerVerificationOnFirstCheckout
{
    public function handle(CheckoutConfirmed $event): void
    {
        $checkout = $event->checkout ?? null;
        if (!$checkout) return;

        $user = $checkout->user ?? null;
        if (!$user) return;

        // Trigger only after first paid order
        $paidOrdersCount = $user->orders()->whereIn("status", ["ACTIVE","COMPLETED","PENDING"])->count();
        if ($paidOrdersCount > 1) return;

        try {
            if (empty($user->email_verified_at)) {
                $user->sendEmailVerification();
            }
        } catch (\Throwable $e) {
            Log::warning("FIRST_CHECKOUT_EMAIL_OTP_FAIL", ["err" => $e->getMessage(), "uid" => $user->id]);
        }
        try {
            if (empty($user->phone_verified_at) && !empty($user->phone)) {
                $user->sendSmsVerification();
            }
        } catch (\Throwable $e) {
            Log::warning("FIRST_CHECKOUT_SMS_OTP_FAIL", ["err" => $e->getMessage(), "uid" => $user->id]);
        }
    }
}
