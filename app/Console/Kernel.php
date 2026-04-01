<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Yedek: anında teslim sipariş onayında tetiklenir; bu satır kalan QUEUED kayıtları temizler
        $schedule->command('app:deliver-localtonet-orders')->everyMinute();
        $schedule->command('app:per-minute-jobs')->everyMinute();
        $schedule->command('app:renew-orders')->dailyAt('10:00');
        $schedule->command('app:invoices-with-upcoming-due-dates')->dailyAt('10:00');
        $schedule->command('app:stop-service-on-unpaid-renew-invoices')->dailyAt('02:00');
        $schedule->command('app:stop-test-product-orders')->everyTenMinutes();
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
