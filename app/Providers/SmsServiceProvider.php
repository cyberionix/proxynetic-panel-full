<?php

namespace App\Providers;

use App\Services\Sms\IletiMerkezi\IletiMerkeziChannel;
use App\Services\SmsService;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(SmsService $smsService)
    {
//        $balance = Cache::remember('balance', 120, function () use ($smsService) {
//            return $smsService->getBalance();
//        });

//        View::share('sms_balance',$balance);
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('sms', function () {
                return new \App\Services\Sms\Mutlucell\MutlucellNotificationChannel();
            });
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}
