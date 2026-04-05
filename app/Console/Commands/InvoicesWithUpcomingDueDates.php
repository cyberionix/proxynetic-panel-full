<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\UpcomingInvoicePaymentNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
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
        foreach ($invoices as $invoice) {
            $cacheKey = 'invoice_reminder_sent_' . $invoice->id . '_' . Carbon::today()->format('Y-m-d');
            if (Cache::has($cacheKey)) continue;

            $invoice->user->notify(new UpcomingInvoicePaymentNotification($invoice));
            \App\Services\NotificationTemplateService::send('invoice_reminder', $invoice->user, [
                'fatura_no' => $invoice->invoice_number ?? $invoice->id,
                'tutar' => number_format($invoice->total ?? 0, 2, ',', '.'),
                'son_odeme_tarihi' => $invoice->due_date?->format('d/m/Y') ?? '',
                'fatura_url' => url('/invoices/' . $invoice->id),
            ]);
            Cache::put($cacheKey, true, now()->endOfDay());
            $count++;
        }
        Log::info('END_INVOICE_WITH_UPCOMING_DUE_DATES', ['invoice_count' => $count]);
    }
}
