<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class UpcomingInvoicePaymentNotification extends Notification
{
    use Queueable, SerializesModels;

    protected $invoice;
    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }
    public function via(object $notifiable): array
    {
        $channels = ["database"];
        if ($notifiable->accept_sms) $channels[] = 'sms';
        if ($notifiable->accept_email) $channels[] = 'mail';
        return $channels;
    }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("#" . $this->invoice->invoice_number . ' Numaralı Faturanızın Son Ödeme Tarihi Yaklaşıyor ' . brand("name"))
            ->view('emails.upcoming_invoice_payment', ["invoice" => $this->invoice]);
    }
    public function toSms($notifiable)
    {
        return "#" . $this->invoice->invoice_number . ' numaralı ' . showBalance($this->invoice->total_price_with_vat) . ' TL tutarındaki faturanızın son ödeme tarihi yaklaşıyor. Son ödeme tarihi: ' . $this->invoice->due_date->format(defaultDateFormat());
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
        return 'upcoming_invoice_payment_notification';
    }
}
