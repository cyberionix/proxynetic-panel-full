<?php

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Events\InvoiceUpdated;
use App\Library\EInvoiceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInvoiceViaEInvoiceManager
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvoiceUpdated $event): void
    {
        $invoice = $event->invoice;
        $EInvoiceManager = app(EInvoiceManager::class);
        $EInvoiceManager->editInvoice($invoice);
    }
}
