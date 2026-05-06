<?php

namespace App\Console\Commands;

use App\Library\Logger;
use App\Models\Order;
use Illuminate\Console\Command;

class DeliverPProxyUSingleOrder extends Command
{
    protected $signature = 'app:deliver-pproxyu-single-order {orderId}';
    protected $description = 'Deliver a single PProxyU order from the pool';

    public function handle(): int
    {
        $orderId = (int) $this->argument('orderId');
        Logger::info('PPROXYU_CMD_START', ['order_id' => $orderId]);

        $order = Order::find($orderId);
        if (!$order) {
            Logger::error('PPROXYU_CMD_ORDER_NOT_FOUND', ['order_id' => $orderId]);
            $this->error("Order #{$orderId} not found.");
            return 1;
        }

        if (!$order->isPProxyUDelivery()) {
            Logger::error('PPROXYU_CMD_NOT_PPROXYU', ['order_id' => $orderId]);
            $this->error("Order #{$orderId} is not a PProxyU order.");
            return 1;
        }

        if ($order->delivery_status === 'DELIVERED') {
            Logger::info('PPROXYU_CMD_ALREADY_DELIVERED', ['order_id' => $orderId]);
            $this->info("Order #{$orderId} is already delivered.");
            return 0;
        }

        $this->info("Delivering PProxyU order #{$orderId} ...");

        $ok = $order->deliverPProxyUOrder();

        if ($ok) {
            Logger::info('PPROXYU_CMD_DONE', ['order_id' => $orderId]);
            $this->info("Order #{$orderId} delivered successfully.");
            return 0;
        }

        Logger::error('PPROXYU_CMD_FAILED', ['order_id' => $orderId]);
        $this->error("Order #{$orderId} delivery failed.");
        return 1;
    }
}
