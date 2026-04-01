<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\UpcomingInvoicePaymentNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
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
    public function handle()
    {
        Log::info('START_INVOICE_WITH_UPCOMING_DUE_DATES');

        $day = 3;

        $invoices = Invoice::whereStatus("PENDING")->whereDate("due_date", "=", Carbon::now()->addDays($day))->get();
        $count = 0;
        foreach ($invoices as $invoice) {
            $invoice->user->notify(new UpcomingInvoicePaymentNotification($invoice));
            $count++;
        }
        Log::info('END_INVOICE_WITH_UPCOMING_DUE_DATES', ['invoice_count' => $count]);
    }
}
