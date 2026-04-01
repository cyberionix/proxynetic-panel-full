<?php

namespace App\Notifications;

use App\Mail\EmailOTPMail;
use App\Services\Sms\IletiMerkezi\IletiMerkeziMessage;
use App\Services\Sms\SMSMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExampleNotify extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['sms','mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new EmailOTPMail([$notifiable, '123123']));
    }

    public function toSms($notifiable)
    {
        return SMSMessage::create()
            ->setBody('Your account was approved!123')
            ->setSendTime(now());
    }

    public function toSystem()
    {

    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function shouldSend()
    {
        return false;
    }
}
