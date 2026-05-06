<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class InvoiceCheckoutConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toSms($notifiable)
    {
        return '#' . $this->invoice->invoice_number . ' nolu, ' . showBalance($this->invoice->total_price_with_vat) . ' TL tutarındaki fatura ödendi.';
    }

    public function toDatabase($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'invoice_checkout_confirmed_notification';
    }
}
