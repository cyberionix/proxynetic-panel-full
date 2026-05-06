<?php

namespace App\Providers;

use App\Library\EInvoiceManager;
use App\Services\ParasutService;
use Illuminate\Support\ServiceProvider;
use Parasut\Client;

class ParasutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ParasutService::class, function ($app) {
            return new ParasutService();
        });
        $this->app->singleton(EInvoiceManager::class,function ($app){
            return new EInvoiceManager($app->make(ParasutService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
