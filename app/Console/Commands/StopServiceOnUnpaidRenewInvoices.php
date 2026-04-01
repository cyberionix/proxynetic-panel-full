<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\ThreeProxyLog;
use App\Notifications\StopServiceOnUnpaidRenewInvoice;
use App\Services\AdminNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StopServiceOnUnpaidRenewInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stop-service-on-unpaid-renew-invoices';

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
        Log::info('START_STOP_SERVICE_ON_UNPAID_RENEW_INVOICES');



        $invoices = Invoice::whereDate("due_date", Carbon::now())
            ->whereStatus("PENDING")
            ->whereHas('items', function($query) {
                $query->where('type', 'RENEW');
            })
            ->with('items')
            ->get();

        $stopOrderCount = 0;
        $stopOrderIds = [];
        $cancelledInvoiceItems = [];
        foreach ($invoices as $invoice){
            foreach ($invoice->items as $invoiceItem){
                if ($invoiceItem->type != "RENEW") continue;
                if (!$invoiceItem->order?->end_date->isPast()) continue;
                if ($invoiceItem->order?->status !== 'ACTIVE') continue;

                $isThreeProxy = $invoiceItem->order->isThreeProxyDelivery();
                $suspendStatus = $isThreeProxy ? 'PASSIVE' : 'CANCELLED';
                $invoiceItem->order->stopService($suspendStatus);

                if ($isThreeProxy) {
                    $pi = $invoiceItem->order->product_info ?? [];
                    ThreeProxyLog::log(
                        $invoiceItem->order->id,
                        ThreeProxyLog::ACTION_EXPIRED,
                        $pi['three_proxy_list'] ?? [],
                        ['reason' => 'unpaid_renew_invoice', 'invoice_id' => $invoice->id],
                        $invoiceItem->order->user_id,
                        $pi['three_proxy_pool_id'] ?? null,
                        $pi['three_proxy_username'] ?? null,
                        $pi['three_proxy_password'] ?? null,
                    );
                }

                $invoice->user->notify(new StopServiceOnUnpaidRenewInvoice($invoiceItem));

                $cancelledInvoiceItems[] = $invoiceItem;
                $stopOrderCount++;
                $stopOrderIds[] = $invoiceItem->order->id;
            }

            $invoice->update(["status" => "CANCELLED"]);
        }

        $orders = Order::whereDate("end_date",'<', Carbon::today())
            ->whereStatus("ACTIVE")
            ->orderByDesc('id')
            ->get();

        foreach ($orders as $order) {
            if (in_array($order->id,$stopOrderIds)) continue;
            if (!$order?->end_date->isPast()) continue;

            $isThreeProxy = $order->isThreeProxyDelivery();
            $suspendStatus = $isThreeProxy ? 'PASSIVE' : 'CANCELLED';
            $order->stopService($suspendStatus);

            if ($isThreeProxy) {
                $pi = $order->product_info ?? [];
                ThreeProxyLog::log(
                    $order->id,
                    ThreeProxyLog::ACTION_EXPIRED,
                    $pi['three_proxy_list'] ?? [],
                    ['reason' => 'end_date_passed'],
                    $order->user_id,
                    $pi['three_proxy_pool_id'] ?? null,
                    $pi['three_proxy_username'] ?? null,
                    $pi['three_proxy_password'] ?? null,
                );
            }
        }
//
        $sendAdminNotify = null;
        if ($stopOrderCount > 0) $sendAdminNotify = AdminNotificationService::stopServiceOnUnpaidRenewInvoices($cancelledInvoiceItems);
        Log::info('END_STOP_SERVICE_ON_UNPAID_RENEW_INVOICES', ["stop_order_count" => $stopOrderCount, "stop_order_ids" => $stopOrderIds, "send_admin_notify" => $sendAdminNotify]);
    }
}
