<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StopServiceOnUnpaidRenewInvoices extends Mailable
{
    use Queueable, SerializesModels;

    public $cancelledInvoiceItems;

    public function __construct($cancelledInvoiceItems)
    {
        $this->cancelledInvoiceItems = $cancelledInvoiceItems;
    }

    public function build()
    {
        return $this->subject(count($this->cancelledInvoiceItems) . ' Adet Hizmet İptal Edildi')
            ->view('emails.admin.stop_service_on_unpaid_renew_invoices', ["cancelledInvoiceItems" => $this->cancelledInvoiceItems]);
    }
}
