<?php

namespace App\Services\Sms\Mutlucell;

use App\Library\Logger;
use App\Models\SmsLog;
use Ardakilic\Mutlucell\Facades\Mutlucell;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class MutlucellNotificationChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);
        $phone_number = $notifiable->routeNotificationFor('sms');
        $aa = Mutlucell::send($phone_number, $message);
        if (Mutlucell::getStatus($aa) === true) {
            SmsLog::create([
                'created_by' => Auth::guard('admin')->check() ? Auth::guard('admin')->id() : null,
                'body' => $message ?? null,
                'number' => $phone_number ?? null,
                'user_id' => $notifiable->id,
                'status' => 'SUCCESS'
            ]);
            return true;
        }

        SmsLog::create([
            'created_by' => Auth::guard('admin')->check() ? Auth::guard('admin')->id() : null,
            'body' => $message ?? null,
            'number' => $phone_number ?? null,
            'user_id' => $notifiable->id,
            'status' => 'ERROR',
        ]);
        Logger::error('SMS_SENT_ERROR_MUTLUCELL', ['message' => $message, 'user_id' => $notifiable->id, 'phone' => $phone_number, 'output' => Mutlucell::parseOutput($aa)]);
    }
}
