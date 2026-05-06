<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\UpcomingInvoicePaymentNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicesWithUpcomingDueDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:invoices-with-upcoming-due-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    private function loadAutoInvoiceSettings(): array
    {
        $path = config_path('auto_invoice_settings.php');
        if (is_file($path)) {
            $data = require $path;
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    public function handle()
    {
        Log::info('START_INVOICE_WITH_UPCOMING_DUE_DATES');

        $settings = $this->loadAutoInvoiceSettings();
        $day = (int) ($settings['reminder_days_before'] ?? 3);

        $invoices = Invoice::whereStatus("PENDING")->whereDate("due_date", "=", Carbon::now()->addDays($day))->get();
        $count = 0;
        $sentToday = DB::table('notifications')
            ->where('type', 'upcoming_invoice_payment_notification')
            ->whereDate('created_at', Carbon::today())
            ->pluck('data')
            ->map(fn($d) => json_decode($d, true)['invoice_id'] ?? null)
            ->filter()
            ->toArray();

        foreach ($invoices as $invoice) {
            if (in_array($invoice->id, $sentToday)) continue;

            $invoice->user->notify(new UpcomingInvoicePaymentNotification($invoice));
            $count++;
        }
        Log::info('END_INVOICE_WITH_UPCOMING_DUE_DATES', ['invoice_count' => $count]);
    }
}
