<?php

namespace App\Console\Commands;

use App\Library\Logger;
use App\Models\Order;
use Illuminate\Console\Command;

class DeliverPProxySingleOrder extends Command
{
    protected $signature = 'app:deliver-pproxy-single-order {orderId}';

    protected $description = 'Tek bir PProxy siparişini arka planda teslim eder';

    public function handle(): int
    {
        $orderId = (int) $this->argument('orderId');
        $order = Order::with('product')->find($orderId);

        if (!$order || !$order->isPProxyDelivery()) {
            $this->error("Sipariş bulunamadı veya PProxy teslimatı değil: {$orderId}");
            return 1;
        }

        if (!in_array($order->delivery_status, ['QUEUED', 'BEING_DELIVERED', 'NOT_DELIVERED'], true)) {
            $this->info("Sipariş kuyrukta değil (status: {$order->delivery_status}), atlanıyor.");
            return 0;
        }

        if ($order->delivery_status === 'NOT_DELIVERED') {
            $order->forceFill(['status' => 'ACTIVE', 'delivery_status' => 'QUEUED', 'delivery_error' => null])->saveQuietly();
            Logger::info('PPROXY_DELIVER_AUTO_RECOVER', ['order_id' => $orderId]);
        }

        Logger::info('PPROXY_DELIVER_START', ['order_id' => $orderId]);

        try {
            $order->forceFill(['delivery_status' => 'BEING_DELIVERED'])->saveQuietly();

            $success = $order->deliverPProxyOrder();

            if ($success) {
                Logger::info('PPROXY_DELIVER_SUCCESS', ['order_id' => $orderId]);
                $this->info("PProxy teslimat tamamlandı.");
                return 0;
            }

            $this->error("PProxy teslimat başarısız: " . ($order->delivery_error ?? 'Bilinmeyen hata'));
            return 1;
        } catch (\Throwable $e) {
            Logger::error('PPROXY_DELIVER_CRASH', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            $order->forceFill([
                'delivery_status' => 'NOT_DELIVERED',
                'delivery_error'  => 'CRASH: ' . $e->getMessage(),
            ])->saveQuietly();
            $this->error("Teslimat çöktü: " . $e->getMessage());
            return 1;
        }
    }
}
