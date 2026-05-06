<?php

namespace App\Traits;

use App\Library\Logger;
use App\Models\ThreeProxyLog;
use App\Models\ThreeProxyPool;
use App\Models\ThreeProxyPoolServer;
use App\Services\ThreeProxyApiService;
use Carbon\Carbon;
use Illuminate\Support\Str;

trait ThreeProxyManagement
{
    public function isThreeProxyDelivery(): bool
    {
        return ($this->product_data['delivery_type'] ?? '') === 'THREEPROXY';
    }

    public function getAllThreeProxyIds(): array
    {
        $pi = $this->product_info ?? [];
        $ids = $pi['three_proxy_ids'] ?? [];
        return is_array($ids) ? array_values(array_filter($ids)) : [];
    }

    public function resolveThreeProxyPool(): ?ThreeProxyPool
    {
        $pi = $this->product_info ?? [];
        $poolId = (int) ($pi['three_proxy_pool_id'] ?? 0);

        if ($poolId <= 0) {
            $product = $this->relationLoaded('product') ? $this->product : $this->product()->first();
            if (!$product) return null;
            $di = $product->delivery_items ?? [];
            $poolId = (int) ($di['three_proxy_pool_id'] ?? 0);
        }

        if ($poolId <= 0) return null;
        return ThreeProxyPool::with('servers')->find($poolId);
    }

    /**
     * Proxy'nin ait olduğu server'ı bulur. product_info'daki server_id'den veya fallback olarak pool'dan.
     */
    private function resolveServerForProxy(string $proxyId): ?ThreeProxyPoolServer
    {
        $pi = $this->product_info ?? [];
        $list = $pi['three_proxy_list'] ?? [];

        foreach ($list as $item) {
            if (($item['proxy_id'] ?? '') === $proxyId && !empty($item['server_id'])) {
                return ThreeProxyPoolServer::find($item['server_id']);
            }
        }

        $pool = $this->resolveThreeProxyPool();
        if ($pool && $pool->servers->count() > 0) {
            return $pool->servers->first();
        }

        return null;
    }

    /**
     * API service'i proxy_id bazlı server'dan veya fallback pool'dan oluşturur.
     */
    private function resolveApiServiceForProxy(string $proxyId): ?ThreeProxyApiService
    {
        $server = $this->resolveServerForProxy($proxyId);
        if ($server) {
            return ThreeProxyApiService::fromServer($server);
        }

        $pool = $this->resolveThreeProxyPool();
        if ($pool) {
            return ThreeProxyApiService::fromPool($pool);
        }

        return null;
    }

    public function threeProxyApprove(): bool
    {
        $this->loadMissing('product');
        $product = $this->product;
        if (!$product) {
            Logger::error('THREEPROXY_APPROVE_PRODUCT_MISSING', ['order_id' => $this->id]);
            return false;
        }

        $di = $product->delivery_items ?? [];
        $poolId = (int) ($di['three_proxy_pool_id'] ?? 0);
        $deliveryCount = max(1, (int) ($di['delivery_count'] ?? 1));

        $pool = ThreeProxyPool::with('servers')->find($poolId);
        if (!$pool) {
            Logger::error('THREEPROXY_POOL_NOT_FOUND', ['order_id' => $this->id, 'pool_id' => $poolId]);
            $this->update(['status' => 'PENDING', 'delivery_status' => 'NOT_DELIVERED', 'delivery_error' => 'THREEPROXY_POOL_NOT_FOUND']);
            return false;
        }

        $allIps = $pool->getAllIpsWithServer();
        if (count($allIps) === 0) {
            Logger::error('THREEPROXY_POOL_EMPTY', ['order_id' => $this->id, 'pool_id' => $poolId]);
            $this->update(['status' => 'PENDING', 'delivery_status' => 'NOT_DELIVERED', 'delivery_error' => 'THREEPROXY_POOL_EMPTY']);
            return false;
        }

        if ($deliveryCount > count($allIps)) {
            Logger::error('THREEPROXY_INSUFFICIENT_IPS', ['order_id' => $this->id, 'needed' => $deliveryCount, 'available' => count($allIps)]);
            $this->update(['status' => 'PENDING', 'delivery_status' => 'NOT_DELIVERED', 'delivery_error' => 'THREEPROXY_INSUFFICIENT_IPS']);
            return false;
        }

        $username = 'u' . Str::random(7);
        $password = Str::random(12);
        $expireDatetime = $this->end_date
            ? $this->end_date->format('Y-m-d') . 'T23:59'
            : Carbon::now()->addDays(30)->format('Y-m-d\TH:i');

        shuffle($allIps);
        $selectedIps = array_slice($allIps, 0, $deliveryCount);

        $result = self::createProxiesOnMultipleServers($pool, $selectedIps, $username, $password, $expireDatetime);

        if (empty($result['success'])) {
            $this->update(['status' => 'PENDING', 'delivery_status' => 'NOT_DELIVERED', 'delivery_error' => 'THREEPROXY_CREATE_ERROR: ' . ($result['message'] ?? '')]);
            return false;
        }

        $proxyList = $result['proxy_list'];
        $proxyIds = array_filter(array_column($proxyList, 'proxy_id'));

        $this->product_info = [
            'three_proxy_ids' => $proxyIds,
            'three_proxy_pool_id' => $poolId,
            'three_proxy_list' => $proxyList,
            'three_proxy_username' => $username,
            'three_proxy_password' => $password,
            'three_proxy_expire' => $expireDatetime,
        ];
        $this->delivery_status = 'DELIVERED';
        $this->status = 'ACTIVE';
        $this->save();

        ThreeProxyLog::log($this->id, ThreeProxyLog::ACTION_CREATED, $proxyList, ['pool_name' => $pool->name, 'expire' => $expireDatetime], $this->user_id, $poolId, $username, $password);
        Logger::info('THREEPROXY_DELIVERY_OK', ['order_id' => $this->id, 'pool_id' => $poolId, 'proxy_count' => count($proxyIds)]);

        return true;
    }

    /**
     * IP'leri server'a göre gruplar, her server'ın API'sine ayrı istek gönderir.
     */
    private static function createProxiesOnMultipleServers(ThreeProxyPool $pool, array $selectedIps, string $username, string $password, string $expireDatetime): array
    {
        $grouped = [];
        foreach ($selectedIps as $item) {
            $serverId = $item['server_id'];
            $grouped[$serverId][] = $item['ip'];
        }

        $allProxyList = [];
        $errors = [];

        foreach ($grouped as $serverId => $ips) {
            $server = ThreeProxyPoolServer::find($serverId);
            if (!$server) {
                $errors[] = "Server #{$serverId} bulunamadı.";
                continue;
            }

            $lines = [];
            foreach ($ips as $ip) {
                // Port atamasını Node.js API'ye bırak (3-parçalı format: IP:USER:PASS)
                // Node.js sunucusundaki tüm port kullanımlarını biliyor, çakışma riski sıfır
                $lines[] = $ip . ':' . $username . ':' . $password;
            }

            $apiService = ThreeProxyApiService::fromServer($server);
            $result = $apiService->createProxy(implode("\n", $lines), $expireDatetime);

            if (empty($result['success'])) {
                $errors[] = $server->server_ip . ': ' . ($result['message'] ?? 'API hatası');
                continue;
            }

            foreach (($result['Proxy_data'] ?? []) as $p) {
                $allProxyList[] = [
                    'proxy_id' => $p['proxy_id'] ?? null,
                    'ip' => $p['ip'] ?? '',
                    'username' => $p['username'] ?? $p['display_username'] ?? '',
                    'password' => $p['password'] ?? '',
                    'http_port' => $p['port'] ?? $p['http_port'] ?? '',
                    'socks_port' => $p['socks_port'] ?? '',
                    'server_id' => $serverId,
                ];
            }
        }

        if (count($allProxyList) === 0) {
            return [
                'success' => false,
                'message' => count($errors) > 0 ? implode('; ', $errors) : 'Hiç proxy oluşturulamadı (API boş yanıt veya Proxy_data yok).',
            ];
        }

        return ['success' => true, 'proxy_list' => $allProxyList, 'errors' => $errors];
    }

    public function threeProxyRevokeApproval(): bool
    {
        $proxyIds = $this->getAllThreeProxyIds();
        $pi = $this->product_info ?? [];
        $poolId = (int) ($pi['three_proxy_pool_id'] ?? 0);

        if (count($proxyIds) === 0) {
            $this->update(['product_info' => null, 'status' => 'CANCELLED', 'delivery_status' => 'NOT_DELIVERED']);
            return true;
        }

        foreach ($proxyIds as $proxyId) {
            $apiService = $this->resolveApiServiceForProxy($proxyId);
            if ($apiService) {
                $apiService->deleteProxy($proxyId);
            }
        }

        $oldList = $pi['three_proxy_list'] ?? [];
        ThreeProxyLog::log($this->id, ThreeProxyLog::ACTION_DELETED, $oldList, ['proxy_ids' => $proxyIds], $this->user_id, $poolId, $pi['three_proxy_username'] ?? null, $pi['three_proxy_password'] ?? null);

        $this->update(['product_info' => null, 'status' => 'CANCELLED', 'delivery_status' => 'NOT_DELIVERED']);
        Logger::info('THREEPROXY_REVOKE_OK', ['order_id' => $this->id, 'proxy_count' => count($proxyIds)]);

        return true;
    }

    public function threeProxyStopService(): void
    {
        $proxyIds = $this->getAllThreeProxyIds();
        if (count($proxyIds) === 0) return;

        foreach ($proxyIds as $proxyId) {
            $apiService = $this->resolveApiServiceForProxy($proxyId);
            if (!$apiService) continue;
            $res = $apiService->stopProxy($proxyId);
            if (empty($res['success'])) {
                Logger::error('THREEPROXY_STOP_FAIL', ['order_id' => $this->id, 'proxy_id' => $proxyId]);
            }
        }

        $pi = $this->product_info ?? [];
        ThreeProxyLog::log($this->id, ThreeProxyLog::ACTION_STOPPED, $pi['three_proxy_list'] ?? [], [], $this->user_id, $pi['three_proxy_pool_id'] ?? null, $pi['three_proxy_username'] ?? null, $pi['three_proxy_password'] ?? null);
    }

    public function threeProxyStartService(): void
    {
        $proxyIds = $this->getAllThreeProxyIds();
        if (count($proxyIds) === 0) return;

        foreach ($proxyIds as $proxyId) {
            $apiService = $this->resolveApiServiceForProxy($proxyId);
            if (!$apiService) continue;
            $res = $apiService->startProxy($proxyId);
            if (empty($res['success'])) {
                Logger::error('THREEPROXY_START_FAIL', ['order_id' => $this->id, 'proxy_id' => $proxyId]);
            }
        }

        $pi = $this->product_info ?? [];
        ThreeProxyLog::log($this->id, ThreeProxyLog::ACTION_STARTED, $pi['three_proxy_list'] ?? [], [], $this->user_id, $pi['three_proxy_pool_id'] ?? null, $pi['three_proxy_username'] ?? null, $pi['three_proxy_password'] ?? null);
    }

    public function threeProxyExtendExpire(string $newExpire): bool
    {
        $proxyIds = $this->getAllThreeProxyIds();
        if (count($proxyIds) === 0) return false;

        $allOk = true;

        foreach ($proxyIds as $proxyId) {
            $apiService = $this->resolveApiServiceForProxy($proxyId);
            if (!$apiService) { $allOk = false; continue; }
            $res = $apiService->updateExpire($proxyId, $newExpire);
            if (empty($res['success'])) {
                Logger::error('THREEPROXY_EXTEND_FAIL', ['order_id' => $this->id, 'proxy_id' => $proxyId]);
                $allOk = false;
            }
        }

        if ($allOk) {
            $pi = $this->product_info ?? [];
            $oldExpire = $pi['three_proxy_expire'] ?? null;
            $pi['three_proxy_expire'] = $newExpire;
            $this->product_info = $pi;
            $this->save();

            ThreeProxyLog::log(
                $this->id,
                ThreeProxyLog::ACTION_EXPIRE_EXTENDED,
                $pi['three_proxy_list'] ?? [],
                ['old_expire' => $oldExpire, 'new_expire' => $newExpire],
                $this->user_id,
                $pi['three_proxy_pool_id'] ?? null,
                $pi['three_proxy_username'] ?? null,
                $pi['three_proxy_password'] ?? null,
            );
        }

        return $allOk;
    }

    public function getThreeProxyDisplayList(): array
    {
        $pi = $this->product_info ?? [];
        return $pi['three_proxy_list'] ?? [];
    }

    private static function generateRandomPort(array $usedPorts): int
    {
        $min = 10000;
        $max = 60000;
        $used = array_flip($usedPorts);
        do {
            $port = random_int($min, $max);
        } while (isset($used[$port]));
        return $port;
    }

    public function threeProxyReinstall(): array
    {
        $pool = $this->resolveThreeProxyPool();
        if (!$pool) {
            return ['success' => false, 'message' => 'Havuz bulunamadı.'];
        }

        $this->loadMissing('product');
        $product = $this->product;
        if (!$product) {
            return ['success' => false, 'message' => 'Ürün bulunamadı.'];
        }

        $di = $product->delivery_items ?? [];
        $deliveryCount = max(1, (int) ($di['delivery_count'] ?? 1));

        $allIps = $pool->getAllIpsWithServer();
        if ($deliveryCount > count($allIps)) {
            return ['success' => false, 'message' => 'Yeterli IP yok. Gerekli: ' . $deliveryCount . ', Mevcut: ' . count($allIps)];
        }

        shuffle($allIps);
        $selectedIps = array_slice($allIps, 0, $deliveryCount);

        $username = 'u' . Str::random(7);
        $password = Str::random(12);
        $expireDatetime = $this->end_date ? $this->end_date->format('Y-m-d') . 'T23:59' : Carbon::now()->addDays(30)->format('Y-m-d\TH:i');

        $result = self::createProxiesOnMultipleServers($pool, $selectedIps, $username, $password, $expireDatetime);

        if (empty($result['success'])) {
            return ['success' => false, 'message' => $result['message'] ?? 'API oluşturma hatası.'];
        }

        $proxyList = $result['proxy_list'];
        $proxyIds = array_filter(array_column($proxyList, 'proxy_id'));

        if (count($proxyIds) < $deliveryCount) {
            return [
                'success' => false,
                'message' => 'Yeniden kurulumda yetersiz proxy: ' . count($proxyIds) . ' / ' . $deliveryCount,
            ];
        }

        $oldIds = $this->getAllThreeProxyIds();
        foreach ($oldIds as $proxyId) {
            $apiService = $this->resolveApiServiceForProxy($proxyId);
            if ($apiService) $apiService->deleteProxy($proxyId);
        }

        $this->product_info = [
            'three_proxy_ids' => $proxyIds,
            'three_proxy_pool_id' => $pool->id,
            'three_proxy_list' => $proxyList,
            'three_proxy_username' => $username,
            'three_proxy_password' => $password,
            'three_proxy_expire' => $expireDatetime,
        ];
        $this->delivery_status = 'DELIVERED';
        $this->status = 'ACTIVE';
        $this->delivery_error = null;
        $this->save();

        ThreeProxyLog::log($this->id, ThreeProxyLog::ACTION_REINSTALLED, $proxyList, ['old_proxy_ids' => $oldIds, 'expire' => $expireDatetime], $this->user_id, $pool->id, $username, $password);
        Logger::info('THREEPROXY_REINSTALL_OK', ['order_id' => $this->id, 'proxy_count' => count($proxyIds)]);

        return ['success' => true, 'message' => count($proxyIds) . ' proxy yeniden kuruldu.'];
    }

    /**
     * IP seçim stratejisine göre yeniden kurulum yapar.
     * @param string $strategy 'subnet' veya 'class'
     */
    public function threeProxyReinstallWithIpStrategy(string $strategy): array
    {
        $pool = $this->resolveThreeProxyPool();
        if (!$pool) {
            return ['success' => false, 'message' => 'Havuz bulunamadı.'];
        }

        $this->loadMissing('product');
        $product = $this->product;
        if (!$product) {
            return ['success' => false, 'message' => 'Ürün bulunamadı.'];
        }

        $di = $product->delivery_items ?? [];
        $deliveryCount = max(1, (int) ($di['delivery_count'] ?? 1));

        $allIps = $pool->getAllIpsWithServer();
        if ($deliveryCount > count($allIps)) {
            return ['success' => false, 'message' => 'Yeterli IP yok. Gerekli: ' . $deliveryCount . ', Mevcut: ' . count($allIps)];
        }

        if ($strategy === 'subnet') {
            $selectedIps = selectIpsBySubnet($allIps, $deliveryCount);
        } elseif ($strategy === 'class') {
            $selectedIps = selectIpsByClass($allIps, $deliveryCount);
        } else {
            shuffle($allIps);
            $selectedIps = array_slice($allIps, 0, $deliveryCount);
        }

        if (count($selectedIps) < $deliveryCount) {
            return ['success' => false, 'message' => 'Strateji (' . $strategy . ') ile yeterli sayıda IP bulunamadı. Bulunan: ' . count($selectedIps) . ', Gerekli: ' . $deliveryCount . '. Havuzda yeterli farklı ' . ($strategy === 'class' ? '/16 blok' : '/24 subnet') . ' yok.'];
        }

        $username = 'u' . Str::random(7);
        $password = Str::random(12);
        $expireDatetime = $this->end_date ? $this->end_date->format('Y-m-d') . 'T23:59' : Carbon::now()->addDays(30)->format('Y-m-d\TH:i');

        $result = self::createProxiesOnMultipleServers($pool, $selectedIps, $username, $password, $expireDatetime);

        if (empty($result['success'])) {
            return ['success' => false, 'message' => $result['message'] ?? 'API oluşturma hatası.'];
        }

        $proxyList = $result['proxy_list'];
        $proxyIds = array_filter(array_column($proxyList, 'proxy_id'));

        if (count($proxyIds) < $deliveryCount) {
            return [
                'success' => false,
                'message' => 'Yeniden kurulumda yetersiz proxy: ' . count($proxyIds) . ' / ' . $deliveryCount,
            ];
        }

        $oldIds = $this->getAllThreeProxyIds();
        foreach ($oldIds as $proxyId) {
            $apiService = $this->resolveApiServiceForProxy($proxyId);
            if ($apiService) $apiService->deleteProxy($proxyId);
        }

        $this->product_info = [
            'three_proxy_ids' => $proxyIds,
            'three_proxy_pool_id' => $pool->id,
            'three_proxy_list' => $proxyList,
            'three_proxy_username' => $username,
            'three_proxy_password' => $password,
            'three_proxy_expire' => $expireDatetime,
        ];
        $this->delivery_status = 'DELIVERED';
        $this->status = 'ACTIVE';
        $this->delivery_error = null;
        $this->save();

        ThreeProxyLog::log($this->id, ThreeProxyLog::ACTION_REINSTALLED, $proxyList, ['old_proxy_ids' => $oldIds, 'expire' => $expireDatetime, 'strategy' => $strategy], $this->user_id, $pool->id, $username, $password);
        Logger::info('THREEPROXY_REINSTALL_WITH_STRATEGY_OK', ['order_id' => $this->id, 'strategy' => $strategy, 'proxy_count' => count($proxyIds)]);

        return ['success' => true, 'message' => count($proxyIds) . ' proxy yeniden kuruldu (' . $strategy . ' stratejisi).'];
    }

    public function threeProxyChangeCredentials(string $newUsername, string $newPassword): array
    {
        $proxyIds = $this->getAllThreeProxyIds();
        if (count($proxyIds) === 0) {
            return ['success' => false, 'message' => 'Güncellenecek proxy bulunamadı.'];
        }

        $successCount = 0;
        $errors = [];

        foreach ($proxyIds as $proxyId) {
            $apiService = $this->resolveApiServiceForProxy($proxyId);
            if (!$apiService) { $errors[] = $proxyId . ': server bulunamadı'; continue; }
            $res = $apiService->updateProxy($proxyId, ['username' => $newUsername, 'password' => $newPassword]);
            if (!empty($res['success'])) {
                $successCount++;
            } else {
                $errors[] = $proxyId . ': ' . ($res['message'] ?? 'bilinmeyen hata');
            }
        }

        if ($successCount > 0) {
            $pi = $this->product_info ?? [];
            $pi['three_proxy_username'] = $newUsername;
            $pi['three_proxy_password'] = $newPassword;
            $list = $pi['three_proxy_list'] ?? [];
            foreach ($list as &$item) {
                $item['username'] = $newUsername;
                $item['password'] = $newPassword;
            }
            unset($item);
            $pi['three_proxy_list'] = $list;
            $this->product_info = $pi;
            $this->save();
        }

        if (count($errors) > 0) {
            Logger::error('THREEPROXY_CHANGE_CRED_PARTIAL', ['order_id' => $this->id, 'success' => $successCount, 'errors' => $errors]);
            return ['success' => $successCount > 0, 'message' => $successCount . '/' . count($proxyIds) . ' proxy güncellendi. Hata: ' . implode('; ', array_slice($errors, 0, 3))];
        }

        $pi = $this->product_info ?? [];
        ThreeProxyLog::log($this->id, ThreeProxyLog::ACTION_CREDENTIALS_CHANGED, $pi['three_proxy_list'] ?? [], ['old_username' => $pi['three_proxy_username'] ?? null, 'new_username' => $newUsername], $this->user_id, $pi['three_proxy_pool_id'] ?? null, $newUsername, $newPassword);
        Logger::info('THREEPROXY_CHANGE_CRED_OK', ['order_id' => $this->id, 'count' => $successCount]);

        return ['success' => true, 'message' => $successCount . ' proxy kullanıcı/şifre güncellendi.'];
    }

    public function threeProxyChangePort(int $newHttpPort, ?int $newSocksPort = null): array
    {
        $proxyIds = $this->getAllThreeProxyIds();
        if (count($proxyIds) === 0) {
            return ['success' => false, 'message' => 'Güncellenecek proxy bulunamadı.'];
        }

        $pi = $this->product_info ?? [];
        $list = $pi['three_proxy_list'] ?? [];
        $successCount = 0;
        $errors = [];

        foreach ($list as $idx => $item) {
            $proxyId = $item['proxy_id'] ?? null;
            if (!$proxyId) continue;

            $apiService = $this->resolveApiServiceForProxy($proxyId);
            if (!$apiService) { $errors[] = ($item['ip'] ?? '') . ': server bulunamadı'; continue; }

            $oldIp = $item['ip'] ?? '';
            $username = $item['username'] ?? ($pi['three_proxy_username'] ?? '');
            $password = $item['password'] ?? ($pi['three_proxy_password'] ?? '');
            $expire = $pi['three_proxy_expire'] ?? '';

            $apiService->deleteProxy($proxyId);

            $proxyLine = $oldIp . ':' . $newHttpPort . ':' . $username . ':' . $password;
            $createRes = $apiService->createProxy($proxyLine, $expire);

            if (!empty($createRes['success']) && !empty($createRes['Proxy_data'])) {
                $newP = $createRes['Proxy_data'][0] ?? [];
                $list[$idx]['proxy_id'] = $newP['proxy_id'] ?? $proxyId;
                $list[$idx]['http_port'] = $newP['port'] ?? $newP['http_port'] ?? $newHttpPort;
                $list[$idx]['socks_port'] = $newP['socks_port'] ?? ($newSocksPort ?? '');
                $successCount++;
            } else {
                $errors[] = $oldIp . ': ' . ($createRes['message'] ?? 'oluşturma hatası');
            }
        }

        if ($successCount > 0) {
            $newIds = array_values(array_filter(array_column($list, 'proxy_id')));
            $pi['three_proxy_list'] = $list;
            $pi['three_proxy_ids'] = $newIds;
            $this->product_info = $pi;
            $this->save();
        }

        if (count($errors) > 0) {
            Logger::error('THREEPROXY_CHANGE_PORT_PARTIAL', ['order_id' => $this->id, 'success' => $successCount, 'errors' => $errors]);
            return ['success' => $successCount > 0, 'message' => $successCount . '/' . count($proxyIds) . ' proxy port güncellendi. Hata: ' . implode('; ', array_slice($errors, 0, 3))];
        }

        $pi = $this->product_info ?? [];
        ThreeProxyLog::log($this->id, ThreeProxyLog::ACTION_PORT_CHANGED, $pi['three_proxy_list'] ?? [], ['new_http_port' => $newHttpPort, 'new_socks_port' => $newSocksPort], $this->user_id, $pi['three_proxy_pool_id'] ?? null, $pi['three_proxy_username'] ?? null, $pi['three_proxy_password'] ?? null);
        Logger::info('THREEPROXY_CHANGE_PORT_OK', ['order_id' => $this->id, 'count' => $successCount, 'new_port' => $newHttpPort]);

        return ['success' => true, 'message' => $successCount . ' proxy port güncellendi (HTTP: ' . $newHttpPort . ').'];
    }
}
