<?php

namespace App\Listeners;

use App\Events\CheckoutConfirmed;
use App\Jobs\ProcessInvoiceItemsWhenCheckoutJob;

/**
 * Ödeme sonrası ağır işi HTTP isteğinde çalıştırmaz: job'ı database (veya env) kuyruğuna atar.
 * Varsayılan: queue.checkout_deferred_connection — php artisan queue:work gerekir.
 */
class ProcessInvoiceItemsWhenCheckout
{
    public function handle(CheckoutConfirmed $event): void
    {
        $connection = config('queue.checkout_deferred_connection', 'database');
        ProcessInvoiceItemsWhenCheckoutJob::dispatch($event->checkout->id)->onConnection($connection);
    }
}
