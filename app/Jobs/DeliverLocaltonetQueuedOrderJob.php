<?php

namespace App\Jobs;

use App\Library\Logger;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class DeliverLocaltonetQueuedOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Çoklu tünel (ör. 1000) için yeterli süre; queue worker timeout’undan da yüksek olmalı. */
    public int $timeout = 7200;

    public int $tries = 3;

    public function __construct(public int $orderId) {}

    public function backoff(): array
    {
        return [120, 600, 1800];
    }

    public function handle(): void
    {
        Order::extendPhpRuntimeForLocaltonetDelivery();

        $order = Order::with('product')->find($this->orderId);
        if (! $order || ! $order->isLocaltonetLikeDelivery()) {
            return;
        }
        if ($order->delivery_status === 'DELIVERED') {
            return;
        }
        if (! in_array($order->delivery_status, ['QUEUED', 'BEING_DELIVERED'], true)) {
            return;
        }

        $order->refresh();
        $order->maybeHealLocaltonetV4DeliveryStatus();
        $order->refresh();
        $order->deliverLocaltonetItem();
    }

    public function failed(Throwable $e): void
    {
        $order = Order::find($this->orderId);
        if (! $order) {
            return;
        }
        if ($order->delivery_status !== 'BEING_DELIVERED') {
            return;
        }

        Logger::error('DELIVER_LOCALTONET_QUEUED_JOB_FAILED', [
            'order_id' => $this->orderId,
            'message' => $e->getMessage(),
        ]);

        $order->update([
            'delivery_status' => 'QUEUED',
            'delivery_error' => 'LOCALTONET_V4_QUEUE_FAILED: '.substr($e->getMessage(), 0, 500),
        ]);
    }
}
