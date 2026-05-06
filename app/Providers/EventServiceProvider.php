<?php

namespace App\Providers;

use App\Events\CheckoutConfirmed;
use App\Events\InvoiceCreated;
use App\Events\InvoiceUpdated;
use App\Events\LocaltonetProxyCreated;
use App\Library\Logger;
use App\Listeners\AddAccessTokenWhenCreateLocaltonetProxy;
use App\Listeners\ProcessInvoiceItemsWhenCheckout;
use App\Listeners\SaveSentEmails;
use App\Listeners\SetAuthenticationWhenCreateLocaltonetProxy;
use App\Listeners\SetBandWidthLimitWhenCreateLocaltonetProxy;
use App\Listeners\SetExpirationDateWhenCreateLocaltonetProxy;
use App\Listeners\SetLocaltonetV4ServerPortWhenCreateLocaltonetProxy;
use App\Listeners\SetProductDeliveryItemsWhenCreateLocaltonetProxy;
use App\Listeners\StartTunnelWhenCreateLocaltonetProxy;
use App\Listeners\UpdateInvoiceViaEInvoiceManager;
use App\Listeners\UpdateProxyTitleWhenCreateLocaltonetProxy;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [

        ],
        CheckoutConfirmed::class => [
            ProcessInvoiceItemsWhenCheckout::class,
        ],
        MessageSent::class => [
            SaveSentEmails::class
        ],
        InvoiceCreated::class => [
//            CreateInvoiceViaEInvoiceManager::class
        ],
        InvoiceUpdated::class => [
            UpdateInvoiceViaEInvoiceManager::class
        ],
        LocaltonetProxyCreated::class => [
            AddAccessTokenWhenCreateLocaltonetProxy::class,
            SetExpirationDateWhenCreateLocaltonetProxy::class,
            SetAuthenticationWhenCreateLocaltonetProxy::class,
            SetBandWidthLimitWhenCreateLocaltonetProxy::class,
            UpdateProxyTitleWhenCreateLocaltonetProxy::class,
            SetProductDeliveryItemsWhenCreateLocaltonetProxy::class,
            // Port çoğu zaman yalnızca tünel başladıktan sonra atanır / API yanıtına düşer.
            StartTunnelWhenCreateLocaltonetProxy::class,
            SetLocaltonetV4ServerPortWhenCreateLocaltonetProxy::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        Event::listen(Login::class, function ($event) {
            $user = $event->user;
            if (Auth::guard("web")->check()){
                $request = request();
                UserSession::create([
                    "user_id" => $user->id,
                    "ip_address" => $request->ip(),
                    "user_agent" => $request->userAgent(),
                    "login_date" => Carbon::now()
                ]);
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
