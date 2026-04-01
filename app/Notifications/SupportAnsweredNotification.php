<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportAnsweredNotification extends Notification
{
    use Queueable;

    protected $support;

    public function __construct($support)
    {
        $this->support = $support;
    }

    public function via(object $notifiable): array
    {
        $channels = ["database"];
//        if ($notifiable->accept_sms) $channels[] = 'sms';
        if ($notifiable->accept_email) $channels[] = 'mail';
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Destek Talebiniz Yanıtlandı | ' . brand("name"))
            ->view('emails.support_answered',['support' => $this->support,'user' => $notifiable]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'support_id' => $this->support->id,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'support_answered_notification';
    }
}
