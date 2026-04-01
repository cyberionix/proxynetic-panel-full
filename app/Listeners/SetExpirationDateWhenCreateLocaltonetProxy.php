<?php

namespace App\Listeners;

use App\Library\Logger;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetExpirationDateWhenCreateLocaltonetProxy
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
            Logger::error("LOCALTONET_SET_EXPIRATION_DATE_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "error_message" => "ProxyId bulunamadı."]);
            return;
        }
        if (!$event->order->end_date) return;
        $endDate = $event->order->end_date->format("Y-m-d") . " " . date("H:i");
        if ($order?->activeDetail?->price?->is_test_product){
            $endDate = Carbon::now()->addHours('2')->format('Y-m-d H:i');
        }

        $service = $order->resolveLocaltonetService();
        $setExpirationDate = $service->setExpirationDateForTunnel($proxyId, Carbon::createFromFormat("Y-m-d H:i", $endDate)->format('Y-m-d\TH:i:s.v\Z'));
        if (@$setExpirationDate["hasError"]) {
            Logger::error("LOCALTONET_SET_EXPIRATION_DATE_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "errorCode" => @$setExpirationDate["errorCode"], "errors" => @$setExpirationDate["errors"]]);
        }
    }
}
