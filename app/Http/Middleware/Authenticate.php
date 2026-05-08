<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($request->is('netAdmin/*') || $request->is('netAdmin')) {
            return route('admin.auth.login');
        }

        // Guest checkout flow: redirect first-time buyer to register page when going to payment
        if ($request->is('portal/basket/payment') || $request->is('portal/basket/payment/*') || $request->is('portal/checkout*') || $request->is('portal/paytr/*')) {
            return route('portal.auth.register');
        }

        return route('portal.auth.login');
    }
}
