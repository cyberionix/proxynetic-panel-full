<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrderStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');

        if (! $order instanceof Order) {
            return $next($request);
        }

        if ($order->status === 'ACTIVE') {
            return $next($request);
        }

        // LOCALTONET V4: bazı hata/timeout sonrası sipariş PENDING kalabiliyor; tünel id’leri yazılmışsa işlemlere izin ver.
        if ($order->isLocaltonetLikeDelivery()
            && $order->isCanDeliveryType('LOCALTONETV4')
            && $order->status === 'PENDING'
            && count($order->getAllLocaltonetProxyIds()) > 0) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Hizmet durumunuz aktif olmadığı için düzenleme yapılamaz. (Hizmet Durumu: '.__(mb_strtolower($order->status)).')',
        ]);
    }
}
