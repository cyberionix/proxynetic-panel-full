<?php

namespace App\Models;

use App\Library\Logger;
use App\Models\ThreeProxyLog;
use App\Services\LocaltonetService;
use App\Traits\LocaltonetManagement;
use App\Traits\OrderEventHandlers;
use App\Traits\PProxyManagement;
use App\Traits\PProxyUManagement;
use App\Traits\StackManagement;
use App\Traits\ThreeProxyManagement;
use Carbon\Carbon;
use Google\Service\AIPlatformNotebooks\DataDisk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes, StackManagement, LocaltonetManagement, ThreeProxyManagement, PProxyManagement, PProxyUManagement, OrderEventHandlers;

    protected $casts = [
        'product_info' => 'json',
        'product_data' => 'json',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_test_product' => 'boolean',
    ];

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::updated(function($order){
            if ($order->status != 'ACTIVE' && $order->delivery_status != 'NOT_DELIVERED'){
                if (($order->isThreeProxyDelivery() || $order->isPProxyDelivery() || $order->isPProxyUDelivery()) && $order->status === 'PASSIVE') {
                    return;
                }
                $order->revokeApproval();
            }
        });
    }

    public function isCanDeliveryType($type)
    {
        return $type == $this->product_data["delivery_type"];
    }

    public function isLocaltonetLikeDelivery(): bool
    {
        return in_array($this->product_data['delivery_type'] ?? '', ['LOCALTONET', 'LOCALTONETV4'], true);
    }

    /**
     * Aynı ürün (havuz) için kullanıcının daha önce teslim aldığı IPv4 havuz IP'leri (tekrar verilmez).
     *
     * @return list<string>
     */
    public static function usedLocaltonetV4PoolIpsForUser(int $userId, int $productId, ?int $excludeOrderId = null): array
    {
        $q = static::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('delivery_status', 'DELIVERED');

        if ($excludeOrderId !== null) {
            $q->where('id', '!=', $excludeOrderId);
        }

        $ips = [];

        foreach ($q->get() as $order) {
            if (! $order->isCanDeliveryType('LOCALTONETV4')) {
                continue;
            }
            $pi = $order->product_info ?? [];
            $snaps = $pi['localtonet_v4_snapshots'] ?? null;
            if (is_array($snaps)) {
                foreach ($snaps as $s) {
                    if (! is_array($s)) {
                        continue;
                    }
                    $ip = trim((string) ($s['selected_ip'] ?? ''));
                    if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                        $ips[$ip] = true;
                    }
                }

                continue;
            }
            $one = $pi['localtonet_v4_snapshot'] ?? null;
            if (is_array($one)) {
                $ip = trim((string) ($one['selected_ip'] ?? ''));
                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ips[$ip] = true;
                }
            }
        }

        return array_keys($ips);
    }

    public function resolveLocaltonetService(): LocaltonetService
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->first();
        if ($this->isCanDeliveryType('LOCALTONETV4') && $product) {
            $di = $product->delivery_items ?? [];
            $url = isset($di['api_url']) ? trim((string) $di['api_url']) : '';
            $key = isset($di['api_key']) ? trim((string) $di['api_key']) : '';

            return new LocaltonetService($url !== '' ? $url : null, $key !== '' ? $key : null);
        }

        return new LocaltonetService();
    }

    public function activeDetail(): HasOne
    {
        return $this->hasOne(OrderDetail::class)->with("price")->where("is_active", 1)->oldest();
    }

    public function activeDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class)->with("price")->where("is_active", 1);
    }

    public function getAllActiveDetailsAdditionalServices()
    {
        $result = [];
        foreach ($this->activeDetails as $detail) {
            foreach ($detail->additional_services as $service) {
                $result[] = $service;
            }
        }

        return $result;
    }

    public function getTotalAmount()
    {
        return @$this->activeDetails[0]->price_data["price_with_vat"];
    }

    public function getPaymentPeriod()
    {
        return __(mb_strtolower(@$this->activeDetails[0]->price_data["duration_unit"]));
    }

    public function approve()
    {
        $product = $this->product;
        if ($product->delivery_type == 'STACK') {
            return $this->stackApprove();
        } else if (in_array($product->delivery_type, ['LOCALTONET', 'LOCALTONETV4'], true)) {
            return $this->localtonetApprove();
        } else if ($product->delivery_type === 'LOCALTONET_ROTATING') {
            return $this->localtonetRotatingApprove();
        } else if ($product->delivery_type === 'THREEPROXY') {
            return $this->threeProxyApprove();
        } else if ($product->delivery_type === 'PPROXY') {
            return $this->pproxyApprove();
        } else if ($product->delivery_type === 'PPROXYU') {
            return $this->pproxyuApprove();
        }
        $this->status = "PENDING";
        $this->delivery_status = "BEING_DELIVERED";
        $this->save();
        return false;
    }

    public function revokeApproval()
    {

        $product = $this->product;
        if (!$product) return true;
        if ($product->delivery_type == 'STACK') {
            return $this->stackRevokeApproval();
        } else if (in_array($product->delivery_type, ['LOCALTONET', 'LOCALTONETV4'], true)) {
            return $this->localtonetRevokeApproval();
        } else if ($product->delivery_type === 'LOCALTONET_ROTATING') {
            return $this->localtonetRotatingRevokeApproval();
        } else if ($product->delivery_type === 'THREEPROXY') {
            return $this->threeProxyRevokeApproval();
        } else if ($product->delivery_type === 'PPROXY') {
            return $this->pproxyRevokeApproval();
        } else if ($product->delivery_type === 'PPROXYU') {
            return $this->pproxyuRevokeApproval();
        }
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProxyListAttribute()
    {
        if ($this->isLocaltonetLikeDelivery()) {
            $proxy = $this->getProxyLocaltonet();
            $result = $proxy["result"] ?? [];
            $remaining = isset($result["bandwidthLimit"]) && is_numeric($result["bandwidthLimit"]) ? "<br><b>Kalan: </b>" . convertByteToGB((@$result["bandwidthLimit"] - @$result["bandwidthUsage"])) . " GB" : "";
            $total = isset($result["bandwidthLimit"]) && is_numeric($result["bandwidthLimit"]) ? convertByteToGB(@$result["bandwidthLimit"]) . " GB" : __("unlimited");

            return "IP : " . @$result["serverIp"] . " <br>PORT : " . @$result["serverPort"] . "<br><br><br><b>Toplam: </b>" . $total . "<br><b>Kullanılan: </b> " . convertByteToGB(@$result["bandwidthUsage"]) . " GB" . $remaining;
        } else if ($this->isThreeProxyDelivery()) {
            $list = $this->getThreeProxyDisplayList();
            $lines = [];
            foreach ($list as $p) {
                $lines[] = ($p['ip'] ?? '') . ':' . ($p['http_port'] ?? '') . ':' . ($p['username'] ?? '') . ':' . ($p['password'] ?? '');
            }
            return $lines;
        } else if ($this->isPProxyDelivery()) {
            $pi = $this->product_info ?? [];
            $ip = $pi['pproxy_server_ip'] ?? '';
            $port = $pi['pproxy_server_port'] ?? '';
            $user = $pi['pproxy_username'] ?? '';
            $pass = $pi['pproxy_password'] ?? '';
            return "IP: {$ip}<br>PORT: {$port}<br>User: {$user}<br>Pass: {$pass}";
        } else if ($this->isPProxyUDelivery()) {
            $pi = $this->product_info ?? [];
            $ip = $pi['pproxyu_pool_ip'] ?? '';
            $port = $pi['pproxyu_pool_port'] ?? '';
            $user = $pi['pproxyu_pool_user'] ?? '';
            $pass = $pi['pproxyu_pool_pass'] ?? '';
            return "IP: {$ip}<br>PORT: {$port}<br>User: {$user}<br>Pass: {$pass}";
        } else {
            return $this->product_info['proxy_list'] ?? [];
        }
    }

    public function drawStatus($customClass = null)
    {
        if ($this->status == "ACTIVE") $color = "success";
        else if ($this->status == "PASSIVE") $color = "danger";
        else $color = "secondary";

        return '<span class="badge badge-' . $color . " " . $customClass . '">' . __(mb_strtolower($this->status)) . '</span>';
    }

    public function drawDeliveryStatus($customClass = null)
    {
        if ($this->delivery_status == "NOT_DELIVERED") $color = "danger";
        else if ($this->delivery_status == "BEING_DELIVERED") $color = "warning";
        else if ($this->delivery_status == "DELIVERED") $color = "success";
        else if ($this->delivery_status == "QUEUED") $color = "info";
        else $color = "";

        return '<span class="badge badge-' . $color . " " . $customClass . '">' . __(mb_strtolower($this->delivery_status)) . '</span>';
    }

    public function lastInvoiceItem()
    {
        return $this->hasOne(InvoiceItem::class)->latest();
    }

    public function threeProxyLogs()
    {
        return $this->hasMany(ThreeProxyLog::class)->latest();
    }

    public function upgradePrices()
    {
        if ($this->status != "ACTIVE" || (!$this->activeDetail || ($this->activeDetail && !$this->activeDetail->price_data))) return null;
        if ($this->activeDetail->price_data["duration_unit"] == "ONE_TIME") return null;

        $totalUseDate = $this->start_date->diffInDays($this->end_date);
        if (!$totalUseDate){
            $totalUseDate = 1;
        }
        $serviceTotalPrice = collect($this->activeDetail->additional_services)->pluck("price")->sum(); // extra aldigi hizmet. Bu yükseltilmis pakette de devam ettigi icin yok sayalım
        $priceWithVat = isset($this->activeDetail->price_data["price_with_vat"]) ? $this->activeDetail->price_data["price_with_vat"] - $serviceTotalPrice : 0;
        $dailyPrice = $priceWithVat / $totalUseDate;
        $unUsedDay = Carbon::now()->diffInDays($this->end_date);

        $remainingPrice = $dailyPrice * $unUsedDay; // yeni paketten dusulmesi gereken miktar (userın bizde kalan parası)

        $priceIds = $this->activeDetail->price_data["upgradeable_price_ids"] ?? [];
        $prices = Price::whereIn("id", $priceIds)->get();
        foreach ($prices as $price) {
            $price->discount = $remainingPrice;
        }

        return count($prices) <= 0 ? null : $prices;
    }

    public function allAdditionalServices()
    {
        $details = OrderDetail::whereOrderId($this->id)->whereIsHidden(0)->get();

        $result = [];
        foreach ($details as $detail) {
            if (is_array($detail->additional_services)){
                foreach ($detail->additional_services as $service) {
                    $service["order_details_id"] = $detail->id;
                    $service["checkout_id"] = $detail->checkout_id;
                    $service["invoice_id"] = $detail->checkout?->invoice_id;

                    $result[] = $service;
                }
            }
        }
        return $result;
    }

    public function stopService($status)
    {
        $this->update([
            "status" => $status
        ]);
        if ($this->isLocaltonetLikeDelivery()) {
            $tunnelIds = $this->getAllLocaltonetProxyIds();
            if (count($tunnelIds) > 0) {
                $this->bulkStopLocaltonetTunnels();
            }
        } else if ($this->isThreeProxyDelivery()) {
            $this->threeProxyStopService();
        } else if ($this->isPProxyDelivery()) {
            $this->pproxyStopService();
        } else if ($this->isPProxyUDelivery()) {
            $this->pproxyuStopService();
        }
    }

    public function startService()
    {
        if ($this->isLocaltonetLikeDelivery()) {
            $this->bulkStartLocaltonetTunnels();
        } else if ($this->isThreeProxyDelivery()) {
            $this->threeProxyStartService();
        } else if ($this->isPProxyDelivery()) {
            $this->pproxyStartService();
        } else if ($this->isPProxyUDelivery()) {
            $this->pproxyuStartService();
        }
        $this->update([
            'status' => 'ACTIVE',
        ]);
    }

    public function bulkStopLocaltonetTunnels(): bool
    {
        $tunnelIds = $this->getAllLocaltonetProxyIds();
        if (count($tunnelIds) === 0) {
            return true;
        }

        $service = $this->resolveLocaltonetService();

        if ($this->isCanDeliveryType('LOCALTONETV4')) {
            $res = $service->bulkStopTunnelsV2(array_map('intval', $tunnelIds));
            if (! empty($res['hasError'])) {
                Logger::error('LOCALTONET_BULK_STOP_FAIL', [
                    'order_id' => $this->id,
                    'errors' => $res['errors'] ?? [],
                ]);
                return false;
            }
            $this->markTunnelsStopped(true);
            return true;
        }

        foreach ($tunnelIds as $proxyId) {
            $response = $service->stopTunnel($proxyId);
            if (@$response['hasError']) {
                Logger::error('LOCALTONET_STOP_TUNNEL_ERROR', [
                    'order_id' => $this->id,
                    'tunnel_id' => $proxyId,
                    'errors' => @$response['errors'],
                ]);
            }
        }
        $this->markTunnelsStopped(true);
        return true;
    }

    public function bulkStartLocaltonetTunnels(): bool
    {
        $tunnelIds = $this->getAllLocaltonetProxyIds();
        if (count($tunnelIds) === 0) {
            return true;
        }

        $service = $this->resolveLocaltonetService();

        if ($this->isCanDeliveryType('LOCALTONETV4')) {
            $res = $service->bulkStartTunnelsV2(array_map('intval', $tunnelIds));
            if (! empty($res['hasError'])) {
                Logger::error('LOCALTONET_BULK_START_FAIL', [
                    'order_id' => $this->id,
                    'errors' => $res['errors'] ?? [],
                ]);
                return false;
            }
            $this->markTunnelsStopped(false);
            return true;
        }

        foreach ($tunnelIds as $proxyId) {
            $response = $service->startTunnel($proxyId);
            if (@$response['hasError']) {
                Logger::error('LOCALTONET_START_TUNNEL_ERROR', [
                    'order_id' => $this->id,
                    'tunnel_id' => $proxyId,
                    'errors' => @$response['errors'],
                ]);
            }
        }
        $this->markTunnelsStopped(false);
        return true;
    }

    public function markTunnelsStopped(bool $stopped): void
    {
        $pi = $this->product_info ?? [];
        $pi['tunnels_stopped'] = $stopped;
        $this->product_info = $pi;
        $this->save();
    }

    public function areTunnelsStopped(): bool
    {
        return (bool) (($this->product_info ?? [])['tunnels_stopped'] ?? false);
    }

    public function deleteServiceAndRevoke()
    {
        if ($this->isLocaltonetLikeDelivery()) {
            $tunnelIds = $this->getAllLocaltonetProxyIds();
            if (count($tunnelIds) > 0) {
                $this->revokeApproval();
            } else {
                $this->update(['status' => 'CANCELLED', 'delivery_status' => 'NOT_DELIVERED']);
            }
        } else if ($this->isThreeProxyDelivery()) {
            $this->revokeApproval();
        } else if ($this->isPProxyDelivery()) {
            $this->revokeApproval();
        } else if ($this->isPProxyUDelivery()) {
            $this->revokeApproval();
        }
    }
}
