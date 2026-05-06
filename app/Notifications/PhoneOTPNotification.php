<?php

namespace App\Notifications;

use App\Services\Sms\SMSMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PhoneOTPNotification extends Notification
{
    use Queueable;
    protected $otpCode;
    public function __construct($otpCode)
    {
        $this->otpCode = $otpCode;
    }
    public function via(object $notifiable):array
    {
        return ['sms'];
    }
    public function toDatabase()
    {
        return [
            'data' => [
                'post_id' => 11,
            ],
        ];
    }
    public function toSms(object $notifiable)
    {
        return 'Doğrulama kodunuz: '.$this->otpCode;
    }
    public function toArray(object $notifiable):array
    {
        return [
            //
        ];
    }
    public function shouldSend()
    {
        return true;
    }
}
