<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationTemplateService
{
    public static function send(string $key, User $user, array $variables = []): void
    {
        try {
            $template = NotificationTemplate::findByKey($key);
            if (!$template || !$template->is_active) {
                return;
            }

            $variables = array_merge([
                'ad' => $user->first_name,
                'soyad' => $user->last_name,
                'email' => $user->email,
                'site_url' => config('app.url', url('/')),
                'site_adi' => config('app.name', 'Proxynetic'),
            ], $variables);

            if ($template->mail_enabled && $user->email) {
                self::sendMail($template, $user, $variables);
            }

            if ($template->sms_enabled && $user->phone) {
                self::sendSms($template, $user, $variables);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationTemplateService hata', [
                'key' => $key,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function sendMail(NotificationTemplate $template, User $user, array $variables): void
    {
        try {
            $subject = $template->renderMailSubject($variables);
            $htmlContent = $template->renderMailContent($variables);

            if (empty($htmlContent)) {
                return;
            }

            Mail::html($htmlContent, function ($message) use ($user, $subject) {
                $message->to($user->email)
                    ->subject($subject);
            });

            self::logEmail($user, $template->key, $subject);
        } catch (\Throwable $e) {
            Log::error('Template mail gönderim hatası', [
                'key' => $template->key,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function sendSms(NotificationTemplate $template, User $user, array $variables): void
    {
        try {
            $settings = self::loadSmsMailSettings();
            if (empty($settings['sms_enabled'])) {
                return;
            }

            $smsContent = $template->renderSms($variables);
            if (empty($smsContent)) {
                return;
            }

            $phone = $user->phone;
            $provider = $settings['sms_provider'] ?? 'mutlucell';

            if ($provider === 'iletimerkezi') {
                self::sendViaIletiMerkezi($settings, $phone, $smsContent, $template->key, $user->id);
            } else {
                self::sendViaMutlucell($settings, $phone, $smsContent, $template->key, $user->id);
            }
        } catch (\Throwable $e) {
            Log::error('Template SMS gönderim hatası', [
                'key' => $template->key,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function sendViaIletiMerkezi(array $settings, string $phone, string $message, string $key, int $userId): void
    {
        $response = Http::timeout(15)->post('https://api.iletimerkezi.com/v1/send-sms/json', [
            'request' => [
                'authentication' => [
                    'key' => $settings['iletimerkezi_key'] ?? '',
                    'hash' => $settings['iletimerkezi_secret'] ?? '',
                ],
                'order' => [
                    'sender' => $settings['iletimerkezi_origin'] ?? '',
                    'sendDateTime' => '',
                    'iys' => 0,
                    'message' => [
                        'text' => $message,
                        'receipents' => ['number' => [$phone]],
                    ],
                ],
            ],
        ]);

        self::logSms($userId, $phone, $message, $key, $response->successful());
    }

    private static function sendViaMutlucell(array $settings, string $phone, string $message, string $key, int $userId): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><smspack/>');
        $xml->addAttribute('ka', $settings['mutlucell_username'] ?? '');
        $xml->addAttribute('pwd', $settings['mutlucell_password'] ?? '');
        $xml->addAttribute('org', $settings['mutlucell_sender'] ?? '');
        $xml->addAttribute('charset', 'turkish');
        $mesaj = $xml->addChild('mesaj');
        $mesaj->addChild('metin', $message);
        $mesaj->addChild('nums', $phone);

        $response = Http::timeout(15)
            ->withBody($xml->asXML(), 'text/xml; charset=UTF-8')
            ->post('https://smsgw.mutlucell.com/smsgw-ws/sndblkex');

        $body = trim($response->body());
        $success = str_starts_with($body, '$') || (is_numeric($body) && (int) $body > 1000);

        self::logSms($userId, $phone, $message, $key, $success);
    }

    private static function logSms(int $userId, string $phone, string $message, string $key, bool $success): void
    {
        try {
            \DB::table('sms_logs')->insert([
                'user_id' => $userId,
                'number' => $phone,
                'body' => $message,
                'status' => $success ? 'SUCCESS' : 'ERROR',
                'error_message' => $success ? null : 'template:' . $key,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ignore log failures
        }
    }

    private static function logEmail(User $user, string $key, string $subject): void
    {
        try {
            \DB::table('email_logs')->insert([
                'user_id' => $user->id,
                'receipt' => $user->email,
                'subject' => $subject,
                'body' => 'template:' . $key,
                'service' => 'template',
                'status' => 'SUCCESS',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ignore log failures
        }
    }

    private static function loadSmsMailSettings(): array
    {
        $path = config_path('sms_mail_settings.php');
        if (is_file($path)) {
            $data = require $path;
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }
}
