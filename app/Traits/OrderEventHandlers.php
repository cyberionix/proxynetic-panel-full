<?php
namespace App\Traits;

use App\Services\AdminNotificationService;
use App\Services\NotificationTemplateService;
use Illuminate\Support\Facades\Auth;

trait OrderEventHandlers
{
    protected static function bootOrderEventHandlers()
    {
        static::created(function ($model) {
            if (!Auth::guard("admin")->check()){
                AdminNotificationService::orderCreated($model);
            }
            if ($model->user) {
                NotificationTemplateService::send('order_received', $model->user, [
                    'siparis_no' => $model->id,
                    'urun_adi' => $model->product?->name ?? ($model->product_data['name'] ?? ''),
                ]);
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

            if ($order->isDirty('status') && $order->user) {
                $vars = [
                    'siparis_no' => $order->id,
                    'urun_adi' => $order->product?->name ?? ($order->product_data['name'] ?? ''),
                    'siparis_url' => url('/my-products/' . $order->id),
                ];
                match ($order->status) {
                    'ACTIVE' => NotificationTemplateService::send(
                        $order->getOriginal('status') === 'PASSIVE' ? 'order_activated' : 'order_confirmed',
                        $order->user, $vars
                    ),
                    'PASSIVE' => NotificationTemplateService::send('order_suspended', $order->user, $vars),
                    'CANCELLED' => NotificationTemplateService::send('order_cancelled', $order->user, $vars),
                    default => null,
                };
            }

            if ($order->isDirty('delivery_status') && $order->delivery_status === 'DELIVERED' && $order->user) {
                if (!($order->isDirty('status') && $order->status === 'ACTIVE')) {
                    NotificationTemplateService::send('order_confirmed', $order->user, [
                        'siparis_no' => $order->id,
                        'urun_adi' => $order->product?->name ?? ($order->product_data['name'] ?? ''),
                        'siparis_url' => url('/my-products/' . $order->id),
                    ]);
                }
            }
        });
    }
}
