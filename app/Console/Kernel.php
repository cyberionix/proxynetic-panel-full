<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    private function loadAutoInvoiceSettings(): array
    {
        $path = config_path('auto_invoice_settings.php');
        if (is_file($path)) {
            $data = require $path;
            if (is_array($data)) return $data;
        }
        return [];
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:deliver-localtonet-orders')->everyMinute();
        $schedule->command('app:per-minute-jobs')->everyMinute();
        $schedule->command('app:stop-test-product-orders')->everyTenMinutes();

        $settings = $this->loadAutoInvoiceSettings();
        $renewTime = $settings['renew_run_time'] ?? '10:00';
        $reminderTime = $settings['reminder_run_time'] ?? '10:00';
        $stopTime = $settings['stop_service_run_time'] ?? '02:00';

        $schedule->command('app:renew-orders')->dailyAt($renewTime);
        $schedule->command('app:invoices-with-upcoming-due-dates')->dailyAt($reminderTime);
        $schedule->command('app:stop-service-on-unpaid-renew-invoices')->dailyAt($stopTime);
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
