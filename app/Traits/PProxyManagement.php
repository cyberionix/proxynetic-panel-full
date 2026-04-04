<?php

namespace App\Traits;

use App\Library\Logger;
use App\Services\PlainProxiesApiService;
use Illuminate\Support\Facades\DB;

trait PProxyManagement
{
    public function isPProxyDelivery(): bool
    {
        return ($this->product_data['delivery_type'] ?? '') === 'PPROXY';
    }

    public function pproxyApprove()
    {
        $this->status = 'ACTIVE';
        $this->delivery_status = 'QUEUED';
        $this->saveQuietly();

        $orderId = $this->id;
        $fn = static function () use ($orderId): void {
            self::dispatchPProxyDelivery($orderId);
        };
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($fn);
        } else {
            $fn();
        }

        return true;
    }

    public function pproxyRevokeApproval()
    {
        try {
            $pi = $this->product_info ?? [];
            $uuid = $pi['pproxy_uuid'] ?? null;

            if ($uuid) {
                $service = new PlainProxiesApiService();
                $service->destroySubUser($uuid);
            }
        } catch (\Throwable $e) {
            Logger::error('PPROXY_REVOKE_FAIL', [
                'order_id' => $this->id,
                'error'    => $e->getMessage(),
            ]);
        }

        $this->forceFill([
            'status'          => 'CANCELLED',
            'delivery_status' => 'NOT_DELIVERED',
        ])->saveQuietly();

        return true;
    }

    public function deliverPProxyOrder(): bool
    {
        try {
            $product = $this->product;
            $di = $product->delivery_items ?? [];

            $bandwidthGb = (float) ($di['pproxy_quota_gb'] ?? 1);
            $days        = (int)   ($di['pproxy_days'] ?? 30);

            $service = new PlainProxiesApiService();
            $result  = $service->provisionOrder($this->id, $bandwidthGb, $days);

            if (!$result) {
                $this->forceFill([
                    'delivery_status' => 'NOT_DELIVERED',
                    'delivery_error'  => 'PProxy API provisioning failed',
                ])->saveQuietly();
                return false;
            }

            $pi = $this->product_info ?? [];
            $pi['pproxy_uuid']        = $result['uuid'];
            $pi['pproxy_username']    = $result['username'];
            $pi['pproxy_password']    = $result['password'];
            $pi['pproxy_server_ip']   = $result['server_ip'];
            $pi['pproxy_server_port'] = $result['server_port'];
            $pi['pproxy_quota_gb']    = $result['quota_gb'];
            $pi['pproxy_days']        = $result['days'];
            $pi['pproxy_raw']         = $result['raw'];

            $this->product_info = $pi;
            $this->delivery_status = 'DELIVERED';
            $this->saveQuietly();

            Logger::info('PPROXY_DELIVER_SUCCESS', ['order_id' => $this->id, 'uuid' => $result['uuid']]);
            return true;
        } catch (\Throwable $e) {
            Logger::error('PPROXY_DELIVER_EXCEPTION', [
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

    public static function dispatchPProxyDelivery(int $orderId): void
    {
        $php     = PHP_BINARY ?: 'php';
        $artisan = base_path('artisan');
        $logFile = base_path('storage/logs/pproxy-delivery-' . $orderId . '.log');

        Logger::info('PPROXY_DISPATCH_BG', ['order_id' => $orderId]);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "start /B \"\" \"{$php}\" \"{$artisan}\" app:deliver-pproxy-single-order {$orderId} > \"{$logFile}\" 2>&1";
        } else {
            $cmd = "{$php} \"{$artisan}\" app:deliver-pproxy-single-order {$orderId} > \"{$logFile}\" 2>&1 &";
        }

        $handle = popen($cmd, 'r');
        if ($handle !== false) {
            pclose($handle);
        } else {
            Logger::error('PPROXY_DISPATCH_BG_FAIL', ['order_id' => $orderId, 'cmd' => $cmd]);
        }
    }

    public function pproxyStopService(): void
    {
        try {
            $pi   = $this->product_info ?? [];
            $uuid = $pi['pproxy_uuid'] ?? null;
            if ($uuid) {
                $service = new PlainProxiesApiService();
                $service->updateSubUserV2($uuid, ['active_until' => now()->toIso8601String()]);
            }
        } catch (\Throwable $e) {
            Logger::error('PPROXY_STOP_FAIL', ['order_id' => $this->id, 'error' => $e->getMessage()]);
        }
    }

    public function pproxyStartService(): void
    {
        try {
            $pi   = $this->product_info ?? [];
            $uuid = $pi['pproxy_uuid'] ?? null;
            $days = $pi['pproxy_days'] ?? 30;
            if ($uuid) {
                $service = new PlainProxiesApiService();
                $newExpire = now()->addDays($days)->toIso8601String();
                $service->updateSubUserV2($uuid, ['active_until' => $newExpire]);
            }
        } catch (\Throwable $e) {
            Logger::error('PPROXY_START_FAIL', ['order_id' => $this->id, 'error' => $e->getMessage()]);
        }
    }

    public function pproxyExtendExpire(int $extraDays = 30): bool
    {
        try {
            $pi   = $this->product_info ?? [];
            $uuid = $pi['pproxy_uuid'] ?? null;
            if (!$uuid) return false;

            $service = new PlainProxiesApiService();
            $newExpire = now()->addDays($extraDays)->toIso8601String();
            $res = $service->updateSubUserV2($uuid, ['active_until' => $newExpire]);

            return $res && ($res['success'] ?? false);
        } catch (\Throwable $e) {
            Logger::error('PPROXY_EXTEND_FAIL', ['order_id' => $this->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function getPProxySubUserData(): ?array
    {
        $pi   = $this->product_info ?? [];
        $uuid = $pi['pproxy_uuid'] ?? null;
        if (!$uuid) return null;

        $cacheKey = 'PPROXY_SUB_' . $uuid;
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($uuid) {
            return $this->fetchPProxySubUserFromApi($uuid);
        });
    }

    public function getPProxySubUserDataFresh(): ?array
    {
        $pi   = $this->product_info ?? [];
        $uuid = $pi['pproxy_uuid'] ?? null;
        if (!$uuid) return null;

        $data = $this->fetchPProxySubUserFromApi($uuid);

        if ($data) {
            $cacheKey = 'PPROXY_SUB_' . $uuid;
            \Illuminate\Support\Facades\Cache::put($cacheKey, $data, 60);
        }

        return $data;
    }

    private function fetchPProxySubUserFromApi(string $uuid): ?array
    {
        $service = new PlainProxiesApiService();
        $res = $service->getSubUserInfo($uuid);
        if ($res && ($res['success'] ?? false)) {
            $body = $res['data'] ?? [];
            $trafficData = $body['data'] ?? [];
            $info = $body['info'] ?? [];

            return [
                'traffic_used'  => $trafficData['traffic_used'] ?? 0,
                'bandwidth'     => $trafficData['bandwidth'] ?? 0,
                'active_until'  => $body['active_until'] ?? null,
                'username'      => $info['ipv4_resi_proxy_username'] ?? null,
                'password'      => $info['ipv4_resi_proxy_password'] ?? null,
                'threads'       => $info['threads'] ?? 0,
                'bps_limit'     => $info['BpsLimit'] ?? 0,
                'product'       => $body['product'] ?? null,
            ];
        }
        return null;
    }
}
