<?php

namespace App\Console\Commands;

use App\Library\EInvoiceManager;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoFormalizeInvoices extends Command
{
    protected $signature = 'invoices:auto-formalize';

    protected $description = 'Ödenen faturaları belirli gün sonrasında otomatik olarak Paraşüt üzerinden resmileştirir.';

    public function handle(EInvoiceManager $manager)
    {
        if (!config('parasut.auto_formalize')) {
            $this->info('Otomatik resmileştirme devre dışı.');
            return 0;
        }

        $days = (int) config('parasut.formalize_days', 3);

        $invoices = Invoice::where('status', 'PAID')
            ->whereNotNull('parasut_id')
            ->whereNull('formalized_at')
            ->where('updated_at', '<=', Carbon::now()->subDays($days))
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('Resmileştirilecek fatura bulunamadı.');
            return 0;
        }

        $this->info("Resmileştirilecek fatura sayısı: {$invoices->count()}");

        $success = 0;
        $fail = 0;

        foreach ($invoices as $invoice) {
            try {
                $result = $manager->formalizeInvoice($invoice);

                if ($result['success'] ?? false) {
                    $success++;
                    $this->line("[OK] Fatura #{$invoice->invoice_number} resmileştirildi.");
                } else {
                    $fail++;
                    $msg = $result['message'] ?? ($result['error'] ?? 'Bilinmeyen hata');
                    $this->warn("[HATA] Fatura #{$invoice->invoice_number}: {$msg}");
                    Log::warning('AUTO_FORMALIZE_FAIL', ['invoice_id' => $invoice->id, 'result' => $result]);
                }
            } catch (\Throwable $e) {
                $fail++;
                $this->error("[EXCEPTION] Fatura #{$invoice->invoice_number}: {$e->getMessage()}");
                Log::error('AUTO_FORMALIZE_EXCEPTION', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Tamamlandı. Başarılı: {$success}, Hatalı: {$fail}");
        return 0;
    }
}
