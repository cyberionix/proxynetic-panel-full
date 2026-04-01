<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewOrderNotification extends Notification
{
    use Queueable;

    protected $invoice, $order;

    public function __construct($invoice, $order)
    {
        $this->invoice = $invoice;
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toSms($notifiable)
    {
        return '#' . $this->order->id . 'nolu siparişinizin, #' . $this->invoice->invoice_number . ' nolu yenileme faturası oluşturuldu.';
    }

    public function toDatabase($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'order_id' => $this->order->id
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'renew_order_notification';
    }
}
