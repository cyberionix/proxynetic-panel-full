<?php

namespace App\Http;

use App\Http\Middleware\CheckInvoiceAddress;
use App\Http\Middleware\CheckProxyVpn;
use App\Http\Middleware\UpdateLastSeen;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        CheckProxyVpn::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
//            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LogRequest::class,
            UpdateLastSeen::class
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'admin' => [
            \App\Http\Middleware\Authenticate::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'logRequest' => \App\Http\Middleware\LogRequest::class,
        'updateLastSeen' => \App\Http\Middleware\UpdateLastSeen::class,
        'check.identity' => \App\Http\Middleware\CheckIdentityNumber::class,
        'check.kyc' => \App\Http\Middleware\CheckKyc::class,
        'check.ban' => \App\Http\Middleware\CheckBan::class,
        'localtonet_forgot_cache_if_action' => \App\Http\Middleware\LocaltonetForgotCacheIfAction::class,
        'check.order_status' => \App\Http\Middleware\CheckOrderStatus::class,
        'check.locked_support' => \App\Http\Middleware\LockedSupportCheck::class,
        'check.verified_phone' => \App\Http\Middleware\CheckVerifiedPhone::class,
        'check.verified_email' => \App\Http\Middleware\CheckVerifiedEmail::class,
        'check.address_created' => CheckInvoiceAddress::class,
        'basic.token' => \App\Http\Middleware\BasicTokenAuth::class,

    ];

}
