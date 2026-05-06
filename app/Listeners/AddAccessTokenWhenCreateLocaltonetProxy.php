<?php

namespace App\Listeners;

use App\Library\Logger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddAccessTokenWhenCreateLocaltonetProxy
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
            Logger::error("LOCALTONET_ADD_ACCESS_CONTROL_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "error_message" => "ProxyId bulunamadı."]);
            return;
        }

        $service = $order->resolveLocaltonetService();
        $addAccessControl = $service->addAccessControl(config("access-controls.bank_urls_string"), $proxyId);
        if (@$addAccessControl["hasError"]) {
            Logger::error("LOCALTONET_ADD_ACCESS_CONTROL_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "errorCode" => @$addAccessControl["errorCode"], "errors" => @$addAccessControl["errors"]]);
        }
    }
}
