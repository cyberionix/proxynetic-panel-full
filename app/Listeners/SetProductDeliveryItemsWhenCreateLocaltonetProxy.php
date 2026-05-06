<?php

namespace App\Listeners;

use App\Library\Logger;
use App\Models\TokenPool;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetProductDeliveryItemsWhenCreateLocaltonetProxy
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
        $product = $order->product;
        if (! $product) {
            return;
        }

        if ($order->isCanDeliveryType('LOCALTONETV4')) {
            // Havuz (v4_entries) teslimattan sonra değiştirilmez; token/IP satırları kalıcıdır.
            return;
        }

        $proxyId = $order->getLocaltonetProxyId();
        if (! $proxyId) {
            Logger::error('LOCALTONET_SET_PRODUCT_DELIVERY_ITEMS_FOUND', ['order_id' => $order->id, 'tunnelId' => $proxyId, 'error_message' => 'ProxyId bulunamadı.']);

            return;
        }

        $authToken = $event->authToken;

        $tokenPool = TokenPool::find($product->delivery_items["token_pool_id"]);
        if (!$tokenPool) return;

        $usedToken = $authToken;
        $newProductAuthTokens = $tokenPool->tokens;
        $newProductAuthTokens = array_filter($newProductAuthTokens, function ($token) use ($usedToken) {
            return $token !== $usedToken;
        });

        $tokenPool->tokens = array_values($newProductAuthTokens);
        $tokenPool->save();
//        $deliveryItems["auth_tokens"] = array_values($newProductAuthTokens);

//        $product->update([
//            "delivery_items" => $deliveryItems
//        ]);
    }
}
