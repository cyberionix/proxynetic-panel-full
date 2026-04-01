<?php

namespace App\Listeners;

use App\Events\LocaltonetProxyCreated;
use App\Library\Logger;

class SetLocaltonetV4ServerPortWhenCreateLocaltonetProxy
{
    public function handle(LocaltonetProxyCreated $event): void
    {
        $order = $event->order;
        if (! $order->isCanDeliveryType('LOCALTONETV4')) {
            return;
        }

        $proxyId = $event->tunnelId !== null ? (int) $event->tunnelId : $order->getLocaltonetProxyId();
        if (! $proxyId) {
            return;
        }

        $service = $order->resolveLocaltonetService();
        if ($service->ensureV4TunnelServerPortAssigned($proxyId)) {
            return;
        }

        Logger::warning('LOCALTONET_V4_PORT_ENSURE_FAILED_AFTER_LISTENER', [
            'order_id' => $order->id,
            'tunnelId' => $proxyId,
        ]);
    }
}
