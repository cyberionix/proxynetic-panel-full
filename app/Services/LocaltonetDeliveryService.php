<?php

namespace App\Services;

use App\Library\Logger;
use App\Models\Order;

class LocaltonetDeliveryService
{
    /**
     * QUEUED Localtonet siparişlerini sırayla teslim eder (cron yedek / toplu işleme).
     *
     * @param  int  $limit  Bir çalıştırmada en fazla işlenecek sipariş
     * @param  float  $sleepSeconds  API hız limiti için isteğe bağlı gecikme (0 = bekleme yok)
     */
    public function processQueuedOrders(int $limit = 30, float $sleepSeconds = 0.0): int
    {
        $orders = Order::whereIn('delivery_status', ['QUEUED', 'BEING_DELIVERED'])
            ->whereHas('product', function ($q) {
                $q->whereIn('delivery_type', ['LOCALTONET', 'LOCALTONETV4']);
            })
            ->with('product')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            return 0;
        }

        Order::extendPhpRuntimeForLocaltonetDelivery();

        Logger::info('LOCALTONET_DELIVER_COMMAND_STARTED', ['count_orders' => $orders->count()]);

        $done = 0;
        foreach ($orders as $order) {
            Logger::info('LOCALTONET_DELIVERING', ['order_id' => $order->id]);
            $order->refresh();
            $order->maybeHealLocaltonetV4DeliveryStatus();
            $order->refresh();
            $order->deliverLocaltonetItem();
            $order->refresh();

            if ($order->delivery_status === 'DELIVERED') {
                $detailCount = count(($order->product_info ?? [])['localtonet_v4_tunnel_details'] ?? []);
                if ($detailCount === 0) {
                    try {
                        $order->fetchAndPersistAllTunnelDetails();
                    } catch (\Throwable $e) {
                        Logger::warning('PERSIST_TUNNEL_DETAILS_FAIL', [
                            'order_id' => $order->id,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $done++;
            if ($sleepSeconds > 0) {
                usleep((int) ($sleepSeconds * 1_000_000));
            }
        }

        Logger::info('LOCALTONET_DELIVER_COMMAND_END', ['count_orders' => $done]);

        return $done;
    }
}
