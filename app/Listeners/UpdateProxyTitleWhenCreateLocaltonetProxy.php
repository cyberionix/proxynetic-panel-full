<?php

namespace App\Listeners;

use App\Library\Logger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProxyTitleWhenCreateLocaltonetProxy
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
        if (! $proxyId) {
            Logger::error('LOCALTONET_UPDATE_TITLE_ERROR', ['order_id' => $order->id, 'tunnelId' => $proxyId, 'error_message' => 'ProxyId bulunamadı.']);

            return;
        }

        $service = $order->resolveLocaltonetService();
        $newProxyTitle = $order->createProxyTitle();
        $tc = isset($event->tunnelCount) ? (int) $event->tunnelCount : 0;
        $ord = isset($event->tunnelOrdinal) ? (int) $event->tunnelOrdinal : 0;
        if ($tc > 1 && $ord >= 1) {
            $newProxyTitle .= ' #'.$ord;
        }

        $updateProxyTitle = $service->updateTitle($proxyId, $newProxyTitle);
        if (@$updateProxyTitle["hasError"]) {
            Logger::error("LOCALTONET_UPDATE_TITLE_ERROR", ["order_id" => $order->id, "tunnelId" => $proxyId, "errorCode" => @$updateProxyTitle["errorCode"], "errors" => @$updateProxyTitle["errors"]]);
        }
    }
}
