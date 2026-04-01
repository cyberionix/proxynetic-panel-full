<?php
namespace App\Traits;

use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;

trait OrderEventHandlers
{
    protected static function bootOrderEventHandlers()
    {
        static::created(function ($model) {
            if (!Auth::guard("admin")->check()){
                AdminNotificationService::orderCreated($model);
            }
        });

        static::updated(function ($order) {
            if ($order->isDirty('end_date') && $order->isLocaltonetLikeDelivery()) {
                $order->localtonetSetExpirationDate($order->end_date);
            }

            if ($order->isDirty('end_date') && $order->isThreeProxyDelivery() && $order->status === 'ACTIVE') {
                $newExpire = $order->end_date->format('Y-m-d') . 'T23:59';
                $order->threeProxyExtendExpire($newExpire);
            }
        });
    }
}
