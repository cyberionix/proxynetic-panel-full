<?php

namespace App\Jobs;

use App\Library\Logger;
use App\Models\BalanceActivity;
use App\Models\Checkout;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ThreeProxyLog;
use App\Services\PlainProxiesApiService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Ödeme sonrası sipariş onayı / Localtonet / 3Proxy — HTTP isteğini bloklamaz.
 * Varsayılan olarak database kuyruğunda çalışır; php artisan queue:work gerekir.
 */
class ProcessInvoiceItemsWhenCheckoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** Uzun Localtonet çağrıları için pay bırakılır; aşırı uzun “RUNNING” için 30 dk yeterli. */
    public int $timeout = 1800;

    public function __construct(public int $checkoutId)
    {
    }

    /**
     * @param  array<string, mixed>|null  $bandwidthLimitConfig
     * @return array{0: float|int, 1: int}
     */
    private static function bandwidthLimitPayloadForTunnel(float $currentBytes, float $additionalGb, ?array $bandwidthLimitConfig): array
    {
        $dataSizeType = (int) (($bandwidthLimitConfig ?? [])['data_size_type'] ?? 4);
        $totalBytes = $currentBytes + ($additionalGb * 1073741824.0);
        if ($totalBytes <= 0) {
            return [0, $dataSizeType];
        }
        if ($dataSizeType === 3) {
            return [(int) max(1, (int) round($totalBytes / 1048576)), $dataSizeType];
        }

        return [(float) round($totalBytes / 1073741824, 2), $dataSizeType];
    }

    private static function forgetLocaltonetTunnelCache(Order $order): void
    {
        foreach ($order->getAllLocaltonetProxyIds() as $tid) {
            Cache::forget('LOCALTONET_PR_DATA_'.$tid);
        }
    }

    public function handle(): void
    {
        $checkout = Checkout::find($this->checkoutId);
        if (!$checkout) {
            return;
        }

        Order::extendPhpRuntimeForLocaltonetDelivery();

        $invoice = $checkout->invoice;
        if (!$invoice) {
            return;
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'PAID']);
        });

        // SQLite: tek uzun transaction içinde HTTP (Localtonet/3Proxy) tutmak tüm DB’yi kilitler;
        // queue worker + tarayıcı aynı anda sqlite’a yazamaz → panel donması. Her save kendi commit’i.
        try {
            $invoice->load(['items.order', 'items.orderDetail']);

            foreach ($invoice->items as $invoiceItem) {
                switch ($invoiceItem->type) {
                    case "NEW":
                        if ($invoiceItem->order) {
                            $invoiceItem->order->approve();
                        }
                        break;
                    case "RENEW":
                        if (!$invoiceItem->orderDetail || !isset($invoiceItem?->orderDetail["price_data"])) {
                            Logger::error('ORDER_DETAIL_PRICE_DATA_NOT_FOUND', ['invoice_id' => $invoiceItem->id]);
                            break;
                        }
                        $priceData = $invoiceItem->orderDetail["price_data"];

                        switch ($priceData["duration_unit"]) {
                            case "DAILY":
                                $newEndDate = $invoiceItem->order->end_date->addDays($priceData["duration"]);
                                break;
                            case "WEEKLY":
                                $newEndDate = $invoiceItem->order->end_date->addWeeks($priceData["duration"]);
                                break;
                            case "MONTHLY":
                                $newEndDate = $invoiceItem->order->end_date->addMonths($priceData["duration"]);
                                break;
                            case "YEARLY":
                                $newEndDate = $invoiceItem->order->end_date->addYears($priceData["duration"]);
                                break;
                            default:
                                $newEndDate = $invoiceItem->order->end_date;
                                Logger::error('ORDER_DETAIL_PRICE_DATA_DURATION_UNIT_NOT_FOUND', ['invoice_item_id' => $invoiceItem->id]);
                                break;
                        }

                        $invoiceItem->order->update([
                            "end_date" => $newEndDate,
                        ]);

                        if ($invoiceItem->order->isThreeProxyDelivery() && $invoiceItem->order->status !== 'ACTIVE') {
                            $invoiceItem->order->startService();
                            $newExpire = $newEndDate->format('Y-m-d') . 'T23:59';
                            $invoiceItem->order->threeProxyExtendExpire($newExpire);

                            $pi = $invoiceItem->order->product_info ?? [];
                            ThreeProxyLog::log(
                                $invoiceItem->order->id,
                                ThreeProxyLog::ACTION_RENEWED,
                                $pi['three_proxy_list'] ?? [],
                                ['new_end_date' => $newEndDate->format('Y-m-d'), 'new_expire' => $newExpire, 'invoice_id' => $invoice->id],
                                $invoiceItem->order->user_id,
                                $pi['three_proxy_pool_id'] ?? null,
                                $pi['three_proxy_username'] ?? null,
                                $pi['three_proxy_password'] ?? null,
                            );

                            Logger::info('THREEPROXY_RENEW_ACTIVATED', [
                                'order_id' => $invoiceItem->order->id,
                                'new_end_date' => $newEndDate->format('Y-m-d'),
                            ]);
                        }

                        OrderDetail::whereOrderId($invoiceItem->order_id)->update(["is_active" => 0]);
                        OrderDetail::whereId($invoiceItem->order_detail_id)->update([
                            "is_active" => 1,
                            "is_hidden" => 0,
                        ]);
                        break;
                    case "UPGRADE":
                        if (!$invoiceItem->orderDetail || !isset($invoiceItem?->orderDetail["price_data"])) {
                            Logger::error('ORDER_DETAIL_PRICE_DATA_NOT_FOUND', ['invoice_id' => $invoiceItem->id]);
                            break;
                        }
                        $priceData = $invoiceItem->orderDetail["price_data"];

                        switch ($priceData["duration_unit"]) {
                            case "DAILY":
                                $newEndDate = $invoiceItem->invoice->invoice_date->addDays($priceData["duration"]);
                                break;
                            case "WEEKLY":
                                $newEndDate = $invoiceItem->invoice->invoice_date->addWeeks($priceData["duration"]);
                                break;
                            case "MONTHLY":
                                $newEndDate = $invoiceItem->invoice->invoice_date->addMonths($priceData["duration"]);
                                break;
                            case "YEARLY":
                                $newEndDate = $invoiceItem->invoice->invoice_date->addYears($priceData["duration"]);
                                break;
                            default:
                                $newEndDate = $invoiceItem->order->end_date;
                                Logger::error('ORDER_DETAIL_PRICE_DATA_DURATION_UNIT_NOT_FOUND', ['invoice_item_id' => $invoiceItem->id]);
                                break;
                        }
                        Order::whereId($invoiceItem->order_id)->update([
                            "start_date" => $invoiceItem->invoice->invoice_date,
                            "end_date" => $newEndDate,
                        ]);
                        $localtonetService = $invoiceItem->order->resolveLocaltonetService();
                        $proxy = $invoiceItem->order->getProxyLocaltonet();
                        $localtonetService->setExpirationDateForTunnel($proxy["result"]["id"], $newEndDate->format('Y-m-d\TH:i:s.v\Z'));
                        OrderDetail::whereOrderId($invoiceItem->order_id)->update(["is_active" => 0]);
                        OrderDetail::whereId($invoiceItem->order_detail_id)->update([
                            "is_active" => 1,
                            "is_hidden" => 0,
                        ]);
                        break;
                    case "BALANCE":
                        $balanceInvoiceItem = $invoice->items->where("type", "BALANCE")->first();
                        $invoice->user->update([
                            "balance" => $invoice->user->balance + $balanceInvoiceItem->total_price_with_vat,
                        ]);
                        BalanceActivity::create([
                            "user_id" => $invoice->user->id,
                            "type" => "IN",
                            "amount" => $balanceInvoiceItem->total_price_with_vat,
                            "model" => "invoice",
                            "model_id" => $invoice->id,
                        ]);
                        break;
                    case "ADDITIONAL_QUOTA":
                        if (!$invoiceItem->order || !$invoiceItem->orderDetail) {
                            Logger::error('PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_ORDER_OR_DETAIL_MISSING_ADDITIONAL_QUOTA', ['invoice_item_id' => $invoiceItem->id]);
                            break;
                        }

                        $additionalService = @$invoiceItem->orderDetail->additional_services[0] ?? null;

                        if (!isset($additionalService["value"])) {
                            Logger::error("PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_ADDITIONAL_SERVICE_NOT_FOUND_ADDITIONAL_QUOTA", ["invoice_item_id" => $invoiceItem->id, "order_detail_id" => $invoiceItem?->order_detail_id]);
                            break;
                        }

                        if ($invoiceItem->order->isPProxyDelivery()) {
                            self::processPProxyAdditionalQuota($invoiceItem, $additionalService);
                        } else {
                            $localtonetService = $invoiceItem->order->resolveLocaltonetService();

                            $proxy = $invoiceItem->order->getProxyLocaltonet();
                            if (!$proxy || !isset($proxy["result"]["id"]) || $proxy["result"]["id"] == 0) {
                                Logger::error("PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_PROXY_NOT_FOUND_ADDITIONAL_QUOTA", ["invoice_item_id" => $invoiceItem->id]);
                                break;
                            }

                            if (@$proxy["result"]["bandwidthLimit"] != "unlimited") {
                                $currentBytes = (float) ($proxy["result"]["bandwidthLimit"] ?? 0);
                                $addGb = (float) $additionalService["value"];
                                $bwCfg = $invoiceItem->order->product_data['delivery_items']['bandwidth_limit'] ?? null;
                                $bwCfg = is_array($bwCfg) ? $bwCfg : [];
                                [$dataSize, $dataSizeType] = self::bandwidthLimitPayloadForTunnel($currentBytes, $addGb, $bwCfg);
                                if ($dataSize <= 0) {
                                    Logger::error('PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_BANDWIDTH_PAYLOAD_ZERO_ADDITIONAL_QUOTA', ['invoice_item_id' => $invoiceItem->id, 'currentBytes' => $currentBytes, 'addGb' => $addGb]);
                                } else {
                                    $setBandwidthLimit = $localtonetService->setBandwidthLimitForTunnel($proxy["result"]["id"], $dataSize, $dataSizeType);
                                    if (@$setBandwidthLimit["hasError"]) {
                                        Logger::error("PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_SET_BANDWIDTH_LIMIT_ADDITIONAL_QUOTA", ["invoice_item_id" => $invoiceItem->id, "tunnelId" => $proxy["result"]["id"], "dataSize" => $dataSize, "dataSizeType" => $dataSizeType, "errorCode" => @$setBandwidthLimit["errorCode"], "errors" => @$setBandwidthLimit["errors"]]);
                                    } else {
                                        self::forgetLocaltonetTunnelCache($invoiceItem->order);
                                    }
                                }
                            }
                        }

                        $invoiceItem->orderDetail->update([
                            "checkout_id" => $checkout->id,
                            "is_active" => 1,
                            "is_hidden" => 0,
                        ]);
                        break;
                    case "ADDITIONAL_QUOTA_DURATION":
                        if (!$invoiceItem->order || !$invoiceItem->orderDetail) {
                            Logger::error('PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_ORDER_OR_DETAIL_MISSING_ADDITIONAL_QUOTA_DURATION', ['invoice_item_id' => $invoiceItem->id]);
                            break;
                        }

                        $localtonetService = $invoiceItem->order->resolveLocaltonetService();

                        $proxy = $invoiceItem->order->getProxyLocaltonet();
                        if (!$proxy || !isset($proxy["result"]["id"]) || $proxy["result"]["id"] == 0) {
                            Logger::error("PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_PROXY_NOT_FOUND_ADDITIONAL_QUOTA_DURATION", ["invoice_item_id" => $invoiceItem->id]);
                            break;
                        }

                        $additionalService = @$invoiceItem->orderDetail->additional_services[0] ?? null;

                        if (!isset($additionalService["value"])) {
                            Logger::error("PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_ADDITIONAL_SERVICE_NOT_FOUND_ADDITIONAL_QUOTA_DURATION", ["invoice_item_id" => $invoiceItem->id, "order_detail_id" => $invoiceItem?->order_detail_id]);
                            break;
                        }
                        if (@$proxy["result"]["bandwidthLimit"] != "unlimited") {
                            $currentBytes = (float) ($proxy["result"]["bandwidthLimit"] ?? 0);
                            $addGb = (float) $additionalService["gb"];
                            $bwCfg = $invoiceItem->order->product_data['delivery_items']['bandwidth_limit'] ?? null;
                            $bwCfg = is_array($bwCfg) ? $bwCfg : [];
                            [$dataSize, $dataSizeType] = self::bandwidthLimitPayloadForTunnel($currentBytes, $addGb, $bwCfg);
                            if ($dataSize <= 0) {
                                Logger::error('PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_BANDWIDTH_PAYLOAD_ZERO_ADDITIONAL_QUOTA_DURATION', ['invoice_item_id' => $invoiceItem->id, 'currentBytes' => $currentBytes, 'addGb' => $addGb]);
                            } else {
                                $setBandwidthLimit = $localtonetService->setBandwidthLimitForTunnel($proxy["result"]["id"], $dataSize, $dataSizeType);
                                if (@$setBandwidthLimit["hasError"]) {
                                    Logger::error("PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_SET_BANDWIDTH_LIMIT_ADDITIONAL_QUOTA_DURATION", ["invoice_item_id" => $invoiceItem->id, "tunnelId" => $proxy["result"]["id"], "dataSize" => $dataSize, "dataSizeType" => $dataSizeType, "errorCode" => @$setBandwidthLimit["errorCode"], "errors" => @$setBandwidthLimit["errors"]]);
                                } else {
                                    self::forgetLocaltonetTunnelCache($invoiceItem->order);
                                }
                            }
                        }

                        switch ($additionalService["duration_unit"]) {
                            case "DAILY":
                                $newEndDate = $invoiceItem->order->end_date->addDays($additionalService["duration"]);
                                break;
                            case "WEEKLY":
                                $newEndDate = $invoiceItem->order->end_date->addWeeks($additionalService["duration"]);
                                break;
                            case "MONTHLY":
                                $newEndDate = $invoiceItem->order->end_date->addMonths($additionalService["duration"]);
                                break;
                            case "YEARLY":
                                $newEndDate = $invoiceItem->order->end_date->addYears($additionalService["duration"]);
                                break;
                            default:
                                $newEndDate = $invoiceItem->order->end_date;
                                Logger::error('PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_ADDITIONAL_SERVICE_DURATION_UNIT_NOT_FOUND_ADDITIONAL_QUOTA_DURATION', ['invoice_item_id' => $invoiceItem->id]);
                                break;
                        }

                        $getExpirationDate = $localtonetService->getExpirationDateByTunnelId($proxy["result"]["id"]);
                        if (!$getExpirationDate || @$getExpirationDate["hasError"] || !isset($getExpirationDate["result"]) || !isset($getExpirationDate["result"]["expirationDate"])) {
                            Logger::error("PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_LOCALTONET_GET_EXPIRATION_DATE_ERROR_ADDITIONAL_QUOTA_DURATION", ["order_id" => $invoiceItem->order->id, "tunnelId" => $proxy["result"]["id"], "errorCode" => @$getExpirationDate["errorCode"], "errors" => @$getExpirationDate["errors"]]);
                            break;
                        }

                        $currentDate = Carbon::parse($getExpirationDate["result"]["expirationDate"])->format("H:i:s");
                        $combinedDateTimeString = $newEndDate->format("Y-m-d") . " " . $currentDate;

                        $newEndDate = Carbon::createFromFormat('Y-m-d H:i:s', $combinedDateTimeString);

                        $setExpirationDate = $localtonetService->setExpirationDateForTunnel($proxy["result"]["id"], $newEndDate->format('Y-m-d\TH:i:s.v\Z'));
                        if (@$setExpirationDate["hasError"]) {
                            Logger::error("PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_LOCALTONET_SET_EXPIRATION_DATE_ERROR_ADDITIONAL_QUOTA_DURATION", ["order_id" => $invoiceItem->order->id, "tunnelId" => $proxy["result"]["id"], "errorCode" => @$setExpirationDate["errorCode"], "errors" => @$setExpirationDate["errors"]]);
                        }

                        $invoiceItem->order->update([
                            "end_date" => $newEndDate->format("Y-m-d"),
                        ]);

                        $invoiceItem->orderDetail->update([
                            "checkout_id" => $checkout->id,
                            "is_active" => 1,
                            "is_hidden" => 0,
                        ]);
                        break;

                    case "TP_EXTRA_DURATION":
                        try {
                            if (!$invoiceItem->order || !$invoiceItem->orderDetail) {
                                Logger::error('TP_EXTRA_DURATION_ORDER_OR_DETAIL_MISSING', ['invoice_item_id' => $invoiceItem->id]);
                                break;
                            }
                            $additionalService = @$invoiceItem->orderDetail->additional_services[0] ?? null;
                            if (!$additionalService || !isset($additionalService["duration"]) || !isset($additionalService["duration_unit"])) {
                                Logger::error('TP_EXTRA_DURATION_SERVICE_DATA_MISSING', ['invoice_item_id' => $invoiceItem->id]);
                                break;
                            }
                            $baseDate = $invoiceItem->order->end_date ?? Carbon::now();
                            switch ($additionalService["duration_unit"]) {
                                case "DAILY": $newEndDate = $baseDate->copy()->addDays((int) $additionalService["duration"]); break;
                                case "WEEKLY": $newEndDate = $baseDate->copy()->addWeeks((int) $additionalService["duration"]); break;
                                case "MONTHLY": $newEndDate = $baseDate->copy()->addMonths((int) $additionalService["duration"]); break;
                                case "YEARLY": $newEndDate = $baseDate->copy()->addYears((int) $additionalService["duration"]); break;
                                default: $newEndDate = $baseDate->copy(); break;
                            }

                            $invoiceItem->order->update(["end_date" => $newEndDate->format("Y-m-d")]);

                            $pi = $invoiceItem->order->product_info ?? [];
                            $pi['three_proxy_expire'] = $newEndDate->format('Y-m-d') . 'T23:59';
                            $invoiceItem->order->product_info = $pi;
                            $invoiceItem->order->save();

                            $invoiceItem->orderDetail->update(["checkout_id" => $checkout->id, "is_active" => 1, "is_hidden" => 0]);

                            $tpPi = $invoiceItem->order->product_info ?? [];
                            ThreeProxyLog::log(
                                $invoiceItem->order->id,
                                ThreeProxyLog::ACTION_EXPIRE_EXTENDED,
                                $tpPi['three_proxy_list'] ?? [],
                                ['new_end_date' => $newEndDate->format('Y-m-d'), 'source' => 'tp_extra_duration_payment'],
                                $invoiceItem->order->user_id,
                                $tpPi['three_proxy_pool_id'] ?? null,
                                $tpPi['three_proxy_username'] ?? null,
                                $tpPi['three_proxy_password'] ?? null,
                            );
                        } catch (\Throwable $e) {
                            Logger::error('TP_EXTRA_DURATION_EXECUTION_FAILED', [
                                'invoice_item_id' => $invoiceItem->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                        break;

                    case "TP_SERVICE_ACTION":
                    case "tp_service_action":
                        if (!$invoiceItem->order || !$invoiceItem->orderDetail) {
                            Logger::error('TP_SERVICE_ACTION_ORDER_OR_DETAIL_MISSING', ['invoice_item_id' => $invoiceItem->id]);
                            break;
                        }
                        $additionalServicesRaw = $invoiceItem->orderDetail->additional_services;
                        $additionalService = is_array($additionalServicesRaw)
                            ? ($additionalServicesRaw[0] ?? $additionalServicesRaw['0'] ?? null)
                            : null;
                        $serviceType = is_array($additionalService)
                            ? ($additionalService['service_type'] ?? $additionalService['value'] ?? null)
                            : null;
                        if ($serviceType === null || $serviceType === '') {
                            Logger::error('TP_SERVICE_ACTION_SERVICE_DATA_MISSING', [
                                'invoice_item_id' => $invoiceItem->id,
                                'additional_services' => $additionalServicesRaw,
                            ]);
                            break;
                        }

                        OrderDetail::where('order_id', $invoiceItem->order_id)
                            ->where('id', '!=', $invoiceItem->order_detail_id)
                            ->update(['is_active' => 0]);

                        $invoiceItem->orderDetail->update([
                            'checkout_id' => $checkout->id,
                            'is_active' => 1,
                            'is_hidden' => 0,
                        ]);

                        try {
                            $order = $invoiceItem->order->fresh(['product']);
                            if (!$order || !$order->isThreeProxyDelivery()) {
                                Logger::error('TP_SERVICE_ACTION_ORDER_INVALID', ['invoice_item_id' => $invoiceItem->id]);
                                break;
                            }

                            $result = null;
                            if ($serviceType === 'tp_change_ips') {
                                $result = $order->threeProxyReinstall();
                            } elseif ($serviceType === 'tp_subnet_ips') {
                                $result = $order->threeProxyReinstallWithIpStrategy('subnet');
                            } elseif ($serviceType === 'tp_class_ips') {
                                $result = $order->threeProxyReinstallWithIpStrategy('class');
                            } else {
                                Logger::error('TP_SERVICE_ACTION_UNKNOWN_SERVICE_TYPE', [
                                    'invoice_item_id' => $invoiceItem->id,
                                    'service_type' => $serviceType,
                                ]);
                                break;
                            }

                            if ($result === null) {
                                break;
                            }

                            if (empty($result['success'])) {
                                Logger::error('TP_SERVICE_ACTION_REINSTALL_FAILED', [
                                    'order_id' => $order->id,
                                    'service_type' => $serviceType,
                                    'message' => $result['message'] ?? '',
                                ]);
                                $order->update([
                                    'delivery_error' => 'TP_REINSTALL: ' . ($result['message'] ?? 'bilinmeyen hata'),
                                ]);
                            }
                        } catch (\Throwable $e) {
                            Logger::error('TP_SERVICE_ACTION_EXECUTION_FAILED', [
                                'order_id' => $invoiceItem->order->id,
                                'service_type' => $serviceType ?? 'unknown',
                                'error' => $e->getMessage(),
                            ]);
                        }
                        break;

                    case "PPROXY_ADDITIONAL_QUOTA":
                        if (!$invoiceItem->order || !$invoiceItem->orderDetail) {
                            Logger::error('PPROXY_ADDITIONAL_QUOTA_ORDER_OR_DETAIL_MISSING', ['invoice_item_id' => $invoiceItem->id]);
                            break;
                        }
                        $additionalService = @$invoiceItem->orderDetail->additional_services[0] ?? null;
                        if (!isset($additionalService["value"])) {
                            Logger::error('PPROXY_ADDITIONAL_QUOTA_SERVICE_DATA_MISSING', ['invoice_item_id' => $invoiceItem->id]);
                            break;
                        }
                        self::processPProxyAdditionalQuota($invoiceItem, $additionalService);
                        $invoiceItem->orderDetail->update([
                            "checkout_id" => $checkout->id,
                            "is_active" => 1,
                            "is_hidden" => 0,
                        ]);
                        break;
                }
            }
        } catch (\Throwable $e) {
            Logger::error('PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT', [
                'checkout_id' => $checkout->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private static function processPProxyAdditionalQuota(InvoiceItem $invoiceItem, array $additionalService): void
    {
        try {
            $order = $invoiceItem->order;
            $pi = $order->product_info ?? [];
            $uuid = $pi['pproxy_uuid'] ?? null;

            if (!$uuid) {
                Logger::error('PPROXY_ADDITIONAL_QUOTA_UUID_MISSING', ['invoice_item_id' => $invoiceItem->id, 'order_id' => $order->id]);
                return;
            }

            $addGb = (float) ($additionalService['value'] ?? 0);
            if ($addGb <= 0) {
                Logger::error('PPROXY_ADDITIONAL_QUOTA_VALUE_INVALID', ['invoice_item_id' => $invoiceItem->id, 'value' => $addGb]);
                return;
            }

            $service = new PlainProxiesApiService();
            $info = $service->getSubUserInfo($uuid);
            $currentBandwidthBytes = $info['data']['data']['bandwidth'] ?? 0;
            $currentBandwidthGb = ceil($currentBandwidthBytes / 1000000000);
            $newTotalGb = $currentBandwidthGb + $addGb;

            $res = $service->setBandwidth($uuid, $newTotalGb);

            if (!$res || !($res['success'] ?? false)) {
                Logger::error('PPROXY_ADDITIONAL_QUOTA_API_FAIL', [
                    'invoice_item_id' => $invoiceItem->id,
                    'order_id'        => $order->id,
                    'current_gb'      => $currentBandwidthGb,
                    'add_gb'          => $addGb,
                    'new_total_gb'    => $newTotalGb,
                    'api_response'    => $res['data'] ?? null,
                ]);
                return;
            }

            $pi['pproxy_quota_gb'] = $newTotalGb;
            $order->product_info = $pi;
            $order->saveQuietly();

            Cache::forget('PPROXY_SUB_' . $uuid);

            Logger::info('PPROXY_ADDITIONAL_QUOTA_SUCCESS', [
                'order_id'     => $order->id,
                'added_gb'     => $addGb,
                'new_total_gb' => $newTotalGb,
            ]);
        } catch (\Throwable $e) {
            Logger::error('PPROXY_ADDITIONAL_QUOTA_EXCEPTION', [
                'invoice_item_id' => $invoiceItem->id,
                'error'           => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        Logger::error('PROCESS_INVOICE_ITEMS_WHEN_CHECKOUT_QUEUE_FAILED', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
