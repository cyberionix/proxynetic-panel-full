<?php

namespace App\Providers;

use App\Services\LocaltonetService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class LocaltonetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LocaltonetService::class,function ($app){
            return new LocaltonetService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $localtonet_servers = Cache::remember('localtonet_servers',300,function (LocaltonetService $localtonetService){
            return $localtonetService->getServers();
        });

        View::share('__localtonet_services',$localtonet_servers);
    }
}
