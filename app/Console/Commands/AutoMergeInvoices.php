<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoMergeInvoices extends Command
{
    protected $signature = 'invoices:auto-merge';
    protected $description = 'Aynı müşteriye ait birden fazla bekleyen yenileme faturasını otomatik birleştirir.';

    public function handle()
    {
        $settings = $this->loadSettings();
        if (!($settings['invoice_consolidation_enabled'] ?? false)) {
            $this->info('Fatura birleştirme devre dışı.');
            return 0;
        }

        $userIds = InvoiceItem::where('type', 'RENEW')
            ->whereHas('invoice', fn($q) => $q->where('status', 'PENDING')->whereNull('deleted_at'))
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereNull('invoices.deleted_at')
            ->where('invoices.status', 'PENDING')
            ->select('invoices.user_id')
            ->groupBy('invoices.user_id')
            ->havingRaw('COUNT(DISTINCT invoices.id) > 1')
            ->pluck('invoices.user_id');

        if ($userIds->isEmpty()) {
            $this->info('Birleştirilecek fatura bulunamadı.');
            return 0;
        }

        $totalMerged = 0;

        foreach ($userIds as $userId) {
            $invoices = Invoice::where('user_id', $userId)
                ->where('status', 'PENDING')
                ->whereHas('items', fn($q) => $q->where('type', 'RENEW'))
                ->orderBy('id')
                ->get();

            if ($invoices->count() < 2) continue;

            DB::beginTransaction();
            try {
                $master = $invoices->first();
                $others = $invoices->slice(1);

                foreach ($others as $other) {
                    InvoiceItem::where('invoice_id', $other->id)
                        ->update(['invoice_id' => $master->id]);
                    $other->delete();
                    $totalMerged++;
                }

                $totals = $master->items()
                    ->selectRaw('SUM(total_price) as total_price, SUM(total_price_with_vat - total_price) as total_vat, SUM(total_price_with_vat) as total_price_with_vat')
                    ->first();

                $master->update([
                    'total_price' => $totals->total_price ?? 0,
                    'total_vat' => $totals->total_vat ?? 0,
                    'total_price_with_vat' => $totals->total_price_with_vat ?? 0,
                ]);

                DB::commit();
                Log::info('AUTO_MERGE_INVOICES', [
                    'user_id' => $userId,
                    'master_invoice_id' => $master->id,
                    'merged_count' => $others->count(),
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('AUTO_MERGE_INVOICES_ERROR', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Toplam {$totalMerged} fatura birleştirildi ({$userIds->count()} müşteri).");
        return 0;
    }

    private function loadSettings(): array
    {
        $path = config_path('auto_invoice_settings.php');
        if (is_file($path)) {
            $data = require $path;
            if (is_array($data)) return $data;
        }
        return [];
    }
}
