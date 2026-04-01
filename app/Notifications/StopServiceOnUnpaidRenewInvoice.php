<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class StopServiceOnUnpaidRenewInvoice extends Notification
{
    use Queueable, SerializesModels;

    protected $invoiceItem;

    public function __construct($invoiceItem)
    {
        $this->invoiceItem = $invoiceItem;
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
            ->subject("#" . $this->invoiceItem->order->id . ' Numaralı Hizmet İptal Edildi ' . brand("name"))
            ->view('emails.stop_service_on_unpaid_renew_invoices', ["invoiceItem" => $this->invoiceItem]);
    }

    public function toSms($notifiable)
    {
        return "#" . $this->invoiceItem->invoice->invoice_number . ' numaralı yenileme faturası ödenmediği #' . $this->invoiceItem->order->id . ' numaralı hizmet iptal edildi';
    }

    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->invoiceItem->order->id,
            'invoice_number' => $this->invoiceItem->invoice->invoice_number,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'stop_service_on_unpaid_renew_invoice';
    }
}
