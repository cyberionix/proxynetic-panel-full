<?php

namespace App\Listeners;

use App\Library\Logger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StartTunnelWhenCreateLocaltonetProxy
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
            Logger::error("LOCALTONET_START_TUNNEL_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "error_message" => "ProxyId bulunamadı."]);
            return;
        }

        $service = $order->resolveLocaltonetService();
        $startTunnel = $service->startTunnel($proxyId);
        if (@$startTunnel["hasError"]) {
            Logger::error("LOCALTONET_START_TUNNEL_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "errorCode" => @$startTunnel["errorCode"], "errors" => @$startTunnel["errors"]]);
        }
    }
}
