<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:deliver-localtonet-orders')->everyMinute();
        $schedule->command('app:per-minute-jobs')->everyMinute();
        $schedule->command('app:stop-test-product-orders')->everyTenMinutes();

        $schedule->command('app:renew-orders')->everyFiveMinutes();
        $schedule->command('app:invoices-with-upcoming-due-dates')->everyFiveMinutes();
        $schedule->command('app:stop-service-on-unpaid-renew-invoices')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
