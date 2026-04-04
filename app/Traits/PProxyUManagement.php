<?php

namespace App\Traits;

use App\Library\Logger;
use App\Models\PProxyUPool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait PProxyUManagement
{
    public function isPProxyUDelivery(): bool
    {
        return ($this->product_data['delivery_type'] ?? '') === 'PPROXYU';
    }

    public function pproxyuApprove()
    {
        $this->status = 'ACTIVE';
        $this->delivery_status = 'QUEUED';
        $this->saveQuietly();

        $delivered = $this->deliverPProxyUOrder();

        if (!$delivered) {
            $orderId = $this->id;
            $fn = static function () use ($orderId): void {
                self::dispatchPProxyUDelivery($orderId);
            };
            if (DB::transactionLevel() > 0) {
                DB::afterCommit($fn);
            } else {
                $fn();
            }
        }

        return true;
    }

    public function pproxyuRevokeApproval()
    {
        $this->forceFill([
            'status'          => 'CANCELLED',
            'delivery_status' => 'NOT_DELIVERED',
        ])->saveQuietly();

        return true;
    }

    public function deliverPProxyUOrder(): bool
    {
        try {
            $product = $this->product;
            $di = $product->delivery_items ?? [];

            $days = (int) ($di['pproxyu_days'] ?? 30);

            $pool = PProxyUPool::where('is_active', true)->inRandomOrder()->first();

            if (!$pool) {
                $this->forceFill([
                    'delivery_status' => 'NOT_DELIVERED',
                    'delivery_error'  => 'PProxyU havuzunda aktif proxy bulunamadı',
                ])->saveQuietly();
                return false;
            }

            $username = 'pnet' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
            $password = Str::random(16);

            $pi = $this->product_info ?? [];
            $pi['pproxyu_pool_id']     = $pool->id;
            $pi['pproxyu_pool_ip']     = $pool->ip;
            $pi['pproxyu_pool_port']   = $pool->port;
            $pi['pproxyu_pool_user']   = $pool->username;
            $pi['pproxyu_pool_pass']   = $pool->password;
            $pi['pproxyu_username']    = $username;
            $pi['pproxyu_password']    = $password;
            $pi['pproxyu_days']        = $days;
            $pi['pproxyu_created_at']  = now()->toIso8601String();
            $pi['pproxyu_active_until'] = now()->addDays($days)->toIso8601String();

            $this->product_info = $pi;
            $this->delivery_status = 'DELIVERED';
            $this->saveQuietly();

            Logger::info('PPROXYU_DELIVER_SUCCESS', ['order_id' => $this->id, 'pool_id' => $pool->id]);
            return true;
        } catch (\Throwable $e) {
            Logger::error('PPROXYU_DELIVER_EXCEPTION', [
                'order_id' => $this->id,
                'error'    => $e->getMessage(),
            ]);
            $this->forceFill([
                'delivery_status' => 'NOT_DELIVERED',
                'delivery_error'  => $e->getMessage(),
            ])->saveQuietly();
            return false;
        }
    }

    public static function dispatchPProxyUDelivery(int $orderId): void
    {
        $php     = PHP_BINARY ?: 'php';
        $artisan = base_path('artisan');
        $logFile = base_path('storage/logs/pproxyu-delivery-' . $orderId . '.log');

        Logger::info('PPROXYU_DISPATCH_BG', ['order_id' => $orderId]);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "start /B \"\" \"{$php}\" \"{$artisan}\" app:deliver-pproxyu-single-order {$orderId} > \"{$logFile}\" 2>&1";
        } else {
            $cmd = "{$php} \"{$artisan}\" app:deliver-pproxyu-single-order {$orderId} > \"{$logFile}\" 2>&1 &";
        }

        $handle = popen($cmd, 'r');
        if ($handle !== false) {
            pclose($handle);
        } else {
            Logger::error('PPROXYU_DISPATCH_BG_FAIL', ['order_id' => $orderId, 'cmd' => $cmd]);
        }
    }

    public function pproxyuStopService(): void
    {
        Logger::info('PPROXYU_STOP', ['order_id' => $this->id]);
    }

    public function pproxyuStartService(): void
    {
        try {
            $pi = $this->product_info ?? [];
            $days = $pi['pproxyu_days'] ?? 30;
            $pi['pproxyu_active_until'] = now()->addDays($days)->toIso8601String();
            $this->product_info = $pi;
            $this->saveQuietly();
        } catch (\Throwable $e) {
            Logger::error('PPROXYU_START_FAIL', ['order_id' => $this->id, 'error' => $e->getMessage()]);
        }
    }

    public function pproxyuExtendExpire(int $extraDays = 30): bool
    {
        try {
            $pi = $this->product_info ?? [];
            $pi['pproxyu_active_until'] = now()->addDays($extraDays)->toIso8601String();
            $this->product_info = $pi;
            $this->saveQuietly();
            return true;
        } catch (\Throwable $e) {
            Logger::error('PPROXYU_EXTEND_FAIL', ['order_id' => $this->id, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
