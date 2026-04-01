<?php

namespace App\Console\Commands;

use App\Library\Logger;
use App\Models\Order;
use Illuminate\Console\Command;

class DeliverLocaltonetSingleOrder extends Command
{
    protected $signature = 'app:deliver-localtonet-single-order {orderId}';

    protected $description = 'Tek bir Localtonet siparişini arka planda teslim eder';

    public function handle(): int
    {
        ini_set('memory_limit', '4096M');
        Order::extendPhpRuntimeForLocaltonetDelivery();

        $orderId = (int) $this->argument('orderId');
        $order = Order::with('product')->find($orderId);

        if (! $order || ! $order->isLocaltonetLikeDelivery()) {
            $this->error("Sipariş bulunamadı veya Localtonet teslimatı değil: {$orderId}");
            return 1;
        }

        if (! in_array($order->delivery_status, ['QUEUED', 'BEING_DELIVERED'], true)) {
            $this->info("Sipariş kuyrukta değil (status: {$order->delivery_status}), atlanıyor.");
            return 0;
        }

        Logger::info('ADMIN_DELIVER_SINGLE_ORDER_START', ['order_id' => $orderId]);

        try {
            return $this->runDelivery($order, $orderId);
        } catch (\Throwable $e) {
            Logger::error('ADMIN_DELIVER_SINGLE_ORDER_CRASH', [
                'order_id' => $orderId,
                'message'  => $e->getMessage(),
                'file'     => $e->getFile().':'.$e->getLine(),
            ]);

            $order->refresh();
            if (in_array($order->delivery_status, ['BEING_DELIVERED'], true) && count($order->getAllLocaltonetProxyIds()) === 0) {
                $order->forceFill(['delivery_status' => 'QUEUED', 'delivery_error' => 'CRASH: '.$e->getMessage()])->save();
            }

            $this->error("Teslimat çöktü: " . $e->getMessage());
            return 1;
        }
    }

    private function runDelivery(Order $order, int $orderId): int
    {
        $order->maybeHealLocaltonetV4DeliveryStatus();
        $order->refresh();

        if ($order->delivery_status === 'DELIVERED') {
            $this->info("Sipariş zaten teslim edilmiş.");
            return 0;
        }

        $order->deliverLocaltonetItem();
        $order->refresh();

        if ($order->delivery_status === 'DELIVERED') {
            Logger::info('ADMIN_DELIVER_SINGLE_ORDER_SUCCESS', ['order_id' => $orderId]);
            $this->info("Teslimat tamamlandı.");
            return 0;
        }

        Logger::error('ADMIN_DELIVER_SINGLE_ORDER_FAIL', [
            'order_id' => $orderId,
            'error'    => $order->delivery_error,
        ]);
        $this->error("Teslimat tamamlanamadı: " . ($order->delivery_error ?: 'Bilinmeyen hata'));
        return 1;
    }
}
