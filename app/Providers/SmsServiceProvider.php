<?php

namespace App\Providers;

use App\Services\Sms\IletiMerkezi\IletiMerkeziChannel;
use App\Services\Sms\Mutlucell\MutlucellNotificationChannel;
use App\Services\SmsService;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function boot(SmsService $smsService)
    {
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('sms', function () {
                $settings = $this->loadSmsMailSettings();
                $provider = $settings['sms_provider'] ?? 'mutlucell';

                if ($provider === 'iletimerkezi') {
                    return new IletiMerkeziChannel([
                        'key' => $settings['iletimerkezi_key'] ?? config('services.sms.iletimerkezi.key'),
                        'secret' => $settings['iletimerkezi_secret'] ?? config('services.sms.iletimerkezi.secret'),
                        'origin' => $settings['iletimerkezi_origin'] ?? config('services.sms.iletimerkezi.origin'),
                        'enable' => true,
                        'debug' => (bool) ($settings['iletimerkezi_debug'] ?? config('services.sms.iletimerkezi.debug', false)),
                        'sandboxMode' => (bool) ($settings['iletimerkezi_sandbox'] ?? config('services.sms.iletimerkezi.sandboxMode', false)),
                    ]);
                }

                $mutlucellConfig = [
                    'auth' => [
                        'username' => $settings['mutlucell_username'] ?? config('mutlucell.auth.username'),
                        'password' => $settings['mutlucell_password'] ?? config('mutlucell.auth.password'),
                    ],
                    'default_sender' => $settings['mutlucell_sender'] ?? config('mutlucell.default_sender'),
                    'charset' => config('mutlucell.charset', 'turkish'),
                    'queue' => false,
                    'append_unsubscribe_link' => false,
                ];

                return new MutlucellNotificationChannel($mutlucellConfig);
            });
        });
    }

    private function loadSmsMailSettings(): array
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

    public function register()
    {
    }
}
