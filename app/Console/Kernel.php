<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:deliver-localtonet-orders')->everyMinute()->withoutOverlapping();
        $schedule->command('app:per-minute-jobs')->everyMinute()->withoutOverlapping();
        $schedule->command('app:stop-test-product-orders')->everyTenMinutes()->withoutOverlapping();

        $schedule->command('app:renew-orders')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('app:invoices-with-upcoming-due-dates')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('app:stop-service-on-unpaid-renew-invoices')->everyFiveMinutes()->withoutOverlapping();

        $schedule->command('invoices:auto-merge')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('invoices:auto-formalize')->daily()->withoutOverlapping();
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
