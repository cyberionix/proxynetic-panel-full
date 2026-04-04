<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Plan;
use App\Models\ProductCategory;
use App\Models\Support;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            $this->app->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            if ($event->connection->getDriverName() !== 'sqlite') {
                return;
            }
            try {
                $event->connection->statement('PRAGMA journal_mode=WAL;');
                $event->connection->statement('PRAGMA busy_timeout=20000;');
            } catch (\Throwable) {
                // bağlantı salt okunur veya izin yoksa yoksay
            }
        });

        App::setLocale('tr');
        Date::setLocale('tr');
        Carbon::setLocale('tr');
        Schema::defaultStringLength(191);
        Str::macro('maskPhone', fn($phoneNumber) => preg_match("/^\d{11}$/", $phoneNumber)
            ? '+90 ' . substr($phoneNumber, 1, 3) . ' ' . substr($phoneNumber, 4, 3) . ' ' . substr($phoneNumber, 7)
            : $phoneNumber);
        Carbon::macro('date', fn($val) => Carbon::make($val)->format(defaultDateFormat()));
        Carbon::macro('dateTime', fn($val) => Carbon::make($val)->format(defaultDateFormat() . ' H:i:s'));
        Carbon::macro('diffText', function ($item) {
            $zaman = strtotime($item);
            $zaman_farki = time() - $zaman;
            $saniye = $zaman_farki;
            $dakika = round($zaman_farki / 60);
            $saat = round($zaman_farki / 3600);
            $gun = round($zaman_farki / 86400);
            $hafta = round($zaman_farki / 604800);
            $ay = round($zaman_farki / 2419200);
            $yil = round($zaman_farki / 29030400);
            if ($saniye < 60) {
                if ($saniye == 0) {
                    return "az önce";
                } else {
                    return $saniye . ' saniye önce';
                }
            } else if ($dakika < 60) {
                return $dakika . ' dakika önce';
            } else if ($saat < 24) {
                return $saat . ' saat önce';
            } else if ($gun < 7) {
                return $gun . ' gün önce';
            } else if ($hafta < 4) {
                return $hafta . ' hafta önce';
            } else if ($ay < 12) {
                return $ay . ' ay önce';
            }
            return '-';
        });

        if (App::environment('local')) {
            Mail::alwaysTo('ahmet@netpus.com.tr');
        }

        view()->composer('*', function ($view) {
            $view->with('_allProductCategories', ProductCategory::where("parent_id", null)->get());
        });

        view()->composer(['portal.static.sidebar', 'portal.static.__sidebar'], function ($view) {
            $portalActiveSupportCount = 0;
            if (auth()->check()) {
                $portalActiveSupportCount = Support::query()
                    ->where('status', '!=', 'RESOLVED')
                    ->count();
            }
            $view->with('portalActiveSupportCount', $portalActiveSupportCount);
        });

        view()->composer('admin.static.sidebar', function ($view) {
            $adminPendingSupportCount = 0;
            if (auth()->guard('admin')->check()) {
                // Dashboard "Bekleyen Destek Talepleri" ile aynı: yanıt bekleyen talepler (WAITING_FOR_AN_ANSWER)
                $adminPendingSupportCount = Support::query()
                    ->where('status', 'WAITING_FOR_AN_ANSWER')
                    ->count();
            }
            $view->with('adminPendingSupportCount', $adminPendingSupportCount);
        });
    }
}
