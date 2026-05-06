<?php

namespace App\Traits;

use App\Events\LocaltonetProxyCreated;
use App\Library\Logger;
use App\Services\LocaltonetService;
use Dflydev\DotAccessData\Data;

trait StackManagement
{
    public function stackApprove()
    {
        $product = $this->product;
        $proxies = $product->delivery_items["proxies"] ?? null;
        if (!$proxies) {
            Logger::error('DELIVERY_ITEM_PROXIES_NOT_FOUND', ['order_id' => $this->id]);
            $this->delivery_status = "NOT_DELIVERED";
            $this->save();
            return false;
        }
        $deliveryCount = $product->delivery_items["delivery_count"] ?? null;
        if (!$deliveryCount) {
            Logger::error('DELIVERY_ITEM_DELIVERY_COUNT_NOT_FOUND', ['order_id' => $this->id]);
            $this->delivery_status = "NOT_DELIVERED";
            $this->save();
            return false;
        }

        $selected_items = array_slice($proxies, 0, $deliveryCount);
        $remaining_items = array_slice($proxies, $deliveryCount);

        $this->product_info = [
            'proxy_list' => $selected_items
        ];

        $this->status = 'ACTIVE';
        $this->delivery_status = 'DELIVERED';
        if ($this->save()) {
            $deliveryItems = $product->delivery_items;
            $deliveryItems["proxies"] = $remaining_items;

            $product->delivery_items = $deliveryItems;
            $product->save();
            return true;
        }
        return false;
    }

    public function stackRevokeApproval()
    {
        $productInfo = $this->product_info;
        $productInfo["proxy_list"] = null;

        $update = $this->update([
            "product_info" => $productInfo,
            "status" => "CANCELLED",
            "delivery_status" => "NOT_DELIVERED"
        ]);

        if (!$update) return false;
        return true;
    }
}
