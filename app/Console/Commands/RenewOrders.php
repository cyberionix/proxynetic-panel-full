<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Notifications\RenewOrderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenewOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:renew-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    function createInvoiceAndOrderDetails($order, $priceData, $additionalServices, $durationUnit)
    {
        DB::beginTransaction();
        try {
            $priceData['price'] = $order->activeDetail->price->price_without_vat;
            $priceData['total_vat'] = ($priceData['price'] * $order->product->vat_percent) / 100;
            $priceData['price_with_vat'] = $priceData['price'] + $priceData['total_vat'];

            $invoice = Invoice::create([
                "invoice_number" => Invoice::generateInvoiceNumber(),
                "invoice_date" => Carbon::now(),
                "due_date" => $order->end_date,
                "status" => "PENDING",
                "total_price" => $priceData['price'],
                "total_vat" => $priceData['total_vat'],
                "total_price_with_vat" => $priceData['price_with_vat'],
                "user_id" => $order->user_id,
            ]);

            $orderDetail = OrderDetail::create([
                "order_id" => $order->id,
                "is_active" => 0,
                "price_data" => $priceData,
                "price_id" => $order->activeDetail->price_id,
                "additional_services" => $additionalServices,
            ]);

            InvoiceItem::create([
                "type" => "RENEW",
                "name" => $order->product->name . " | " . $order->activeDetail->price->duration . " " . __(mb_strtolower($durationUnit)),
                "total_price" => $priceData['price'],
                "vat_percent" => $order->product->vat_percent,
                "total_price_with_vat" => $priceData['price_with_vat'],
                "product_id" => $order->product_id,
                "price_id" => $order->activeDetail->price_id,
                "order_id" => $order->id,
                "order_detail_id" => $orderDetail->id,
                "invoice_id" => $invoice->id,
            ]);

            $invoice->user->notify(new RenewOrderNotification($invoice, $order));
            \App\Services\NotificationTemplateService::send('order_renewed', $invoice->user, [
                'siparis_no' => $order->id,
                'urun_adi' => $order->product?->name ?? '',
                'fatura_no' => $invoice->invoice_number ?? $invoice->id,
                'tutar' => number_format($invoice->total_price_with_vat ?? 0, 2, ',', '.'),
                'son_odeme_tarihi' => $invoice->due_date?->format('d/m/Y') ?? '',
                'fatura_url' => url('/invoices/' . $invoice->id),
            ]);
            DB::commit();
            Log::info('CRON_RENEW_ORDERS', ["invoice_id" => $invoice->id, "order_id" => $order->id, "order_item_id" => $orderDetail->id]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('CRON_RENEW_ORDERS', ['order_id' => $order->id, "error" => $e->getMessage()]);
        }
    }
    function processOrders($orders, $durationUnit = null)
    {
        if ($orders) {
            foreach ($orders as $order) {
                if (!$priceData = $order->activeDetail->price) continue;
                $priceData = $order->activeDetail->price->toArray();
                if ($durationUnit) {
                    if ($priceData["duration_unit"] != $durationUnit) continue;
                } else {
                    if ($priceData["duration_unit"] == "DAILY" || $priceData["duration_unit"] == "WEEKLY") continue;
                }
                unset($priceData['deleted_at']);
                unset($priceData['created_at']);
                unset($priceData['updated_at']);

                $additionalServices = null;
                foreach ($order->getAllActiveDetailsAdditionalServices() as $service) {
                    if (isset($service["renew"]) && $service["renew"]) {
                        $additionalServices[] = getAdditionalServices($order->product, $service["name"], $service["value"]);
                    }
                }

                $this->createInvoiceAndOrderDetails($order, $priceData, $additionalServices, $priceData["duration_unit"]);
            }
        }
    }
    public function handle()
    {
        Log::info('START_CRON_RENEW_ORDERS');

        /** Günlük ve Haftalıklar Hariç **/

        $remainingDay = 7;
        $orders1 = Order::with("activeDetail")
            ->whereDeliveryStatus("DELIVERED")
            ->whereStatus("ACTIVE")
            ->whereDate('end_date', Carbon::now()->addDays($remainingDay)->format("Y-m-d"))
            ->get();

        $this->processOrders($orders1);

        /** Haftalıklar İçin **/
        $remainingDay = 2;
        $orders2 = Order::with("activeDetail")
            ->whereDeliveryStatus("DELIVERED")
            ->whereStatus("ACTIVE")
            ->whereDate('end_date', Carbon::now()->addDays($remainingDay)->format("Y-m-d"))
            ->get();

        $this->processOrders($orders2, "WEEKLY");


        $orderCount = count($orders1) + count($orders2);
        Log::info('END_CRON_RENEW_ORDERS', ["order_count" => $orderCount]);
    }

}
