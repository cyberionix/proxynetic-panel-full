<?php

namespace App\Services\Sms\Mutlucell;

use App\Library\Logger;
use App\Models\SmsLog;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MutlucellNotificationChannel
{
    private array $config;

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = $config;
        } else {
            $this->config = [
                'auth' => [
                    'username' => config('mutlucell.auth.username'),
                    'password' => config('mutlucell.auth.password'),
                ],
                'default_sender' => config('mutlucell.default_sender'),
                'charset' => config('mutlucell.charset', 'turkish'),
            ];
        }
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);
        $phone_number = $notifiable->routeNotificationFor('sms');

        $username = $this->config['auth']['username'] ?? '';
        $password = $this->config['auth']['password'] ?? '';
        $sender = $this->config['default_sender'] ?? '';
        $charset = $this->config['charset'] ?? 'turkish';

        try {
            $xmlEl = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><smspack/>');
            $xmlEl->addAttribute('ka', $username);
            $xmlEl->addAttribute('pwd', $password);
            $xmlEl->addAttribute('org', $sender);
            $xmlEl->addAttribute('charset', $charset);
            $mesaj = $xmlEl->addChild('mesaj');
            $mesaj->addChild('metin', $message);
            $mesaj->addChild('nums', $phone_number);
            $xml = $xmlEl->asXML();

            $response = Http::timeout(15)
                ->withBody($xml, 'text/xml; charset=UTF-8')
                ->post('https://smsgw.mutlucell.com/smsgw-ws/sndblkex');

            $body = trim($response->body());
            $isSuccess = str_starts_with($body, '$') || (is_numeric($body) && (int) $body > 1000);

            SmsLog::create([
                'created_by' => Auth::guard('admin')->check() ? Auth::guard('admin')->id() : null,
                'body' => $message,
                'number' => $phone_number,
                'user_id' => $notifiable->id,
                'status' => $isSuccess ? 'SUCCESS' : 'ERROR',
                'error_message' => $isSuccess ? null : mb_substr($body, 0, 500),
            ]);

            if (!$isSuccess) {
                Logger::error('SMS_SENT_ERROR_MUTLUCELL', [
                    'message' => $message,
                    'user_id' => $notifiable->id,
                    'phone' => $phone_number,
                    'response' => mb_substr($body, 0, 500),
                ]);
            }

            return $isSuccess;
        } catch (\Throwable $e) {
            SmsLog::create([
                'created_by' => Auth::guard('admin')->check() ? Auth::guard('admin')->id() : null,
                'body' => $message,
                'number' => $phone_number,
                'user_id' => $notifiable->id,
                'status' => 'ERROR',
                'error_message' => $e->getMessage(),
            ]);

            Logger::error('SMS_SENT_EXCEPTION_MUTLUCELL', [
                'message' => $message,
                'user_id' => $notifiable->id,
                'phone' => $phone_number,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
