<?php

namespace App\Listeners;

use App\Library\Logger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetBandWidthLimitWhenCreateLocaltonetProxy
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $order = $event->order;
        $proxyId = isset($event->tunnelId) && $event->tunnelId !== null ? (int) $event->tunnelId : $order->getLocaltonetProxyId();
        if (!$proxyId){
            Logger::error("LOCALTONET_SET_BANDWIDTH_LIMIT_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "error_message" => "ProxyId bulunamadı."]);
            return;
        }

        $service = $order->resolveLocaltonetService();
        $productBandwidthLimit = $order->product_data["delivery_items"]["bandwidth_limit"] ?? null;

        $dataSize     = $productBandwidthLimit["data_size"] ?? null;
        $dataSizeType = $productBandwidthLimit["data_size_type"] ?? null;

        if (empty($dataSize) || intval($dataSize) <= 0) {
            return;
        }

        $setBandwidthLimit = $service->setBandwidthLimitForTunnel($proxyId, $dataSize, $dataSizeType);

        if (@$setBandwidthLimit["hasError"]) {
            Logger::error("LOCALTONET_SET_BANDWIDTH_LIMIT_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "errorCode" => @$setBandwidthLimit["errorCode"], "errors" => @$setBandwidthLimit["errors"]]);
        }
    }
}
