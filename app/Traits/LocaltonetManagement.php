<?php

namespace App\Traits;

use App\Events\LocaltonetProxyCreated;
use App\Exceptions\LocaltonetException;
use App\Library\Logger;
use App\Services\LocaltonetService;
use Carbon\Carbon;
use App\Jobs\DeliverLocaltonetQueuedOrderJob;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait LocaltonetManagement
{
    protected $localtonetService;

    public function getAllLocaltonetProxyIds(): array
    {
        $pi = $this->product_info ?? [];
        $ids = $pi['localtonet_v4_proxy_ids'] ?? null;
        if ($this->isCanDeliveryType('LOCALTONETV4') && is_array($ids) && count($ids) > 0) {
            return array_values(array_filter(array_map('intval', $ids)));
        }
        $one = isset($pi['proxy_id']) ? (int) $pi['proxy_id'] : 0;

        return $one > 0 ? [$one] : [];
    }

    public function orderOwnsLocaltonetTunnelId(int $tunnelId): bool
    {
        return in_array($tunnelId, $this->getAllLocaltonetProxyIds(), true);
    }

    /**
     * Müşteri paneli + Localtonet API: V4’te tünel id’leri DB’de kayıtlıysa teslim bayrağı gecikse de işlem açılır.
     * Bazı hata/timeout senaryolarında sipariş PENDING kalabiliyor; id’ler yazılmışsa yine gösterilir.
     */
    public function isLocaltonetPortalOperationsAllowed(): bool
    {
        if (! $this->isLocaltonetLikeDelivery()) {
            return false;
        }

        $hasTunnelIds = count($this->getAllLocaltonetProxyIds()) > 0;

        $statusOk = $this->status === 'ACTIVE'
            || ($this->isCanDeliveryType('LOCALTONETV4') && $this->status === 'PENDING' && $hasTunnelIds);

        if (! $statusOk) {
            return false;
        }

        if ($this->delivery_status === 'DELIVERED') {
            return true;
        }

        return $this->isCanDeliveryType('LOCALTONETV4') && $hasTunnelIds;
    }

    /**
     * Tüneller oluşmuş ama delivery_status güncellenmemiş siparişleri DELIVERED yapar (job/timeout sonrası).
     * DB'de hiç tünel id yoksa Localtonet listesinden externalId (order-{sipariş}-{sıra}) ile kurtarma dener.
     */
    public function maybeHealLocaltonetV4DeliveryStatus(): void
    {
        if (! $this->isCanDeliveryType('LOCALTONETV4') || ! $this->product) {
            return;
        }
        if (! in_array($this->status, ['ACTIVE', 'PENDING'], true)) {
            return;
        }

        $this->loadMissing('product');

        // DB'de id yok: API'de tam externalId kümesi varsa (özellikle QUEUED + job DB yazamadı) kurtar.
        if (count($this->getAllLocaltonetProxyIds()) === 0
            && in_array($this->delivery_status, ['QUEUED', 'BEING_DELIVERED', 'NOT_DELIVERED'], true)) {
            $this->tryRecoverLocaltonetV4ProductInfoFromRemote();
            $this->refresh();
        }

        if ($this->delivery_status === 'DELIVERED') {
            return;
        }
        if ($this->status !== 'ACTIVE') {
            return;
        }
        if (! in_array($this->delivery_status, ['QUEUED', 'BEING_DELIVERED', 'NOT_DELIVERED'], true)) {
            return;
        }
        $ids = $this->getAllLocaltonetProxyIds();
        $expected = (int) ($this->product->delivery_items['delivery_count'] ?? 1);
        if ($expected < 1) {
            $expected = 1;
        }
        if (count($ids) < 1) {
            return;
        }
        $full = count($ids) === $expected;
        $this->forceFill([
            'delivery_status' => 'DELIVERED',
            'delivery_error' => $full ? null : 'PARTIAL_DELIVERY:'.count($ids).'/'.$expected,
        ])->save();
    }

    /**
     * DB'de V4 tünel id'leri yok; Localtonet GET /v2/tunnels ile yalnızca externalId order-{sipariş}-{sıra} eşleştirir.
     * (Başlık eşleşmesi aynı ürün adında yanlış tünelleri bağlayabildiği için kullanılmaz.)
     */
    public function tryRecoverLocaltonetV4ProductInfoFromRemote(): bool
    {
        if (! $this->isCanDeliveryType('LOCALTONETV4')) {
            return false;
        }
        if (count($this->getAllLocaltonetProxyIds()) > 0) {
            return false;
        }
        $this->loadMissing('product');
        if (! $this->product) {
            return false;
        }
        if (! in_array($this->status, ['ACTIVE', 'PENDING', 'BEING_DELIVERED'], true)) {
            return false;
        }

        $lock = Cache::lock('localtonet_v4_recover_order_'.$this->id, 90);
        if (! $lock->get()) {
            return false;
        }

        try {
            $this->refresh();
            if (count($this->getAllLocaltonetProxyIds()) > 0) {
                return false;
            }

            $expected = (int) ($this->product->delivery_items['delivery_count'] ?? 1);
            if ($expected < 1) {
                $expected = 1;
            }
            if ($expected > 1000) {
                $expected = 1000;
            }

            $service = $this->resolveLocaltonetService();
            $listRes = $service->getAllTunnelsV2();
            if (! empty($listRes['hasError'])) {
                Logger::error('LOCALTONET_V4_REMOTE_RECOVER_LIST_FAILED', [
                    'order_id' => $this->id,
                    'errors' => $listRes['errors'] ?? [],
                ]);

                return false;
            }
            $tunnels = $listRes['tunnels'] ?? [];
            if (! is_array($tunnels)) {
                return false;
            }

            $ids = LocaltonetService::orderedTunnelIdsForOrderFromExternalIds((int) $this->id, $tunnels);

            if ($this->delivery_status === 'QUEUED') {
                // Yarım oluşmuş teslimatla yarışmayı önlemek için: yalnızca 0..N-1 tam externalId kümesi.
                if (! LocaltonetService::externalIdRecoverySetIsCompleteForOrder((int) $this->id, $tunnels, $expected)
                    || count($ids) !== $expected) {
                    return false;
                }
            } elseif (count($ids) < 1) {
                Logger::info('LOCALTONET_V4_REMOTE_RECOVER_NO_MATCH', [
                    'order_id' => $this->id,
                    'expected' => $expected,
                    'via_external' => 0,
                ]);

                return false;
            }

            $snapshots = $this->buildV4SnapshotsFromTunnelListRows($ids, $tunnels);

            $pi = is_array($this->product_info) ? $this->product_info : [];
            $pi['proxy_id'] = $ids[0];
            $pi['localtonet_v4_proxy_ids'] = $ids;
            $pi['localtonet_v4_snapshots'] = $snapshots;
            $pi['localtonet_v4_snapshot'] = $snapshots[0] ?? [];

            $this->product_info = $pi;
            $this->status = 'ACTIVE';
            $full = count($ids) === $expected;
            $this->delivery_status = 'DELIVERED';
            $this->delivery_error = $full ? null : ('LOCALTONET_V4_RECOVERED_PARTIAL:'.count($ids).'/'.$expected);
            $this->save();

            foreach ($ids as $tid) {
                Cache::forget('LOCALTONET_PR_DATA_'.(int) $tid);
            }

            try {
                $this->fetchAndPersistAllTunnelDetails();
            } catch (\Throwable $e) {
                Logger::warning('LOCALTONET_V4_RECOVER_PERSIST_DETAILS_FAIL', [
                    'order_id' => $this->id,
                    'message' => $e->getMessage(),
                ]);
            }

            Logger::info('LOCALTONET_V4_REMOTE_RECOVER_OK', [
                'order_id' => $this->id,
                'tunnel_count' => count($ids),
                'expected' => $expected,
            ]);

            return true;
        } finally {
            $lock->release();
        }
    }

    /**
     * @param  list<int>  $orderedIds
     * @param  list<mixed>  $tunnels
     * @return list<array{token: string, ips: array, selected_ip: string}>
     */
    protected function buildV4SnapshotsFromTunnelListRows(array $orderedIds, array $tunnels): array
    {
        $byId = [];
        foreach ($tunnels as $t) {
            if (! is_array($t)) {
                continue;
            }
            $tid = LocaltonetService::extractTunnelIdFromBulkV2Row($t);
            if ($tid !== null && $tid > 0) {
                $byId[$tid] = $t;
            }
        }

        $out = [];
        foreach ($orderedIds as $tid) {
            $row = $byId[$tid] ?? [];
            $out[] = [
                'token' => (string) ($row['authToken'] ?? $row['AuthToken'] ?? ''),
                'ips' => [],
                'selected_ip' => trim((string) ($row['localServerIp'] ?? $row['LocalServerIp'] ?? '')),
            ];
        }

        return $out;
    }

    protected function shouldPersistLocaltonetAuthToProductInfo(): bool
    {
        if (! $this->isCanDeliveryType('LOCALTONETV4')) {
            return true;
        }
        $ids = $this->product_info['localtonet_v4_proxy_ids'] ?? null;

        return ! (is_array($ids) && count($ids) > 1);
    }

    /**
     * @return array|false Tam API sarmalayıcı (result, hasError, …); boş/hata durumunda false veya [].
     */
    public function getLocaltonetTunnelDetailCached(int $tunnelId)
    {
        if (! $this->isLocaltonetLikeDelivery() || $tunnelId <= 0) {
            return false;
        }

        $persistAuth = $this->shouldPersistLocaltonetAuthToProductInfo();
        $orderId = $this->id;

        $localtonet_proxy_data = Cache::remember('LOCALTONET_PR_DATA_'.$tunnelId, 90, function () use ($tunnelId, $persistAuth, $orderId) {
            $service = $this->resolveLocaltonetService();
            $proxy = $service->getTunnelDetail($tunnelId);
            if (@$proxy['hasError'] || ! isset($proxy['result']) || @$proxy['result']['id'] == 0) {
                Logger::error('LOCALTONET_GET_TUNNEL_DETAIL_ERROR', ['order_id' => $orderId, 'errorCode' => @$proxy['errorCode'], 'errors' => @$proxy['errors']]);

                return false;
            }

            if (isset($proxy['result']['bandwidthLimit']) && is_numeric($proxy['result']['bandwidthLimit'])) {
                if ($proxy['result']['bandwidthLimit'] > 0) {
                    $bandwidthLimit = $proxy['result']['bandwidthLimit'];
                } else {
                    $bandwidthLimit = 'unlimited';
                }
            } else {
                $bandwidthLimit = 0;
            }

            $proxy['result']['bandwidthLimit'] = $bandwidthLimit;
            $proxy['result']['bandwidthUsage'] = isset($proxy['result']['bandwidthUsage']) && is_numeric($proxy['result']['bandwidthUsage']) ? $proxy['result']['bandwidthUsage'] : @$proxy['result']['bandwidthUsage'];

            if ($proxy['result']['bandwidthLimit'] == 0 || $proxy['result']['bandwidthLimit'] == 'unlimited') {
                $proxy['result']['bgBandwidthUsage'] = null;
            } else {
                $usagePercentage = ($proxy['result']['bandwidthUsage'] / $proxy['result']['bandwidthLimit']) * 100;
                $bg = null;
                if ($usagePercentage == 100) {
                    $bg = 'danger';
                } elseif ($usagePercentage >= 60) {
                    $bg = 'warning';
                }
                $proxy['result']['bgBandwidthUsage'] = $bg;
            }

            $getAuthentication = $service->getAuthenticationDataByTunnelId($tunnelId);

            if ($getAuthentication['hasError']) {
                Logger::error('LOCALTONET_GET_AUTHENTICATION_DATA_ERROR', ['order_id' => $orderId, 'errorCode' => @$getAuthentication['errorCode'], 'errors' => @$getAuthentication['errors']]);
            } else {
                $getAuthenticationResult = $getAuthentication['result'] ?? null;
                $proxy['result']['authentication']['isActive'] = $getAuthenticationResult['isActive'] ?? null;
                $proxy['result']['authentication']['userName'] = $getAuthenticationResult['userName'] ?? null;
                $proxy['result']['authentication']['password'] = $getAuthenticationResult['password'] ?? null;

                if ($persistAuth) {
                    $product_info = $this->product_info;

                    $product_info['authentication'] = [
                        'ip' => $proxy['result']['serverIp'] ?? '',
                        'port' => $proxy['result']['serverPort'] ?? '',
                        'username' => $proxy['result']['authentication']['userName'],
                        'password' => $proxy['result']['authentication']['password'],
                    ];

                    $this->update([
                        'product_info' => $product_info,
                    ]);
                }
            }

            if (function_exists('localtonet_tunnel_result_is_socks') && localtonet_tunnel_result_is_socks($proxy['result'])) {
                $proxy['result']['drawProtocolType'] = 'Socks5';
            } else {
                $pt = $proxy['result']['protocolType'] ?? $proxy['result']['ProtocolType'] ?? null;
                $httpCode = (int) config('services.localtonet_v4.v2_protocol_http', 6);
                if ($pt === 'ProxyHttp' || (is_numeric($pt) && (int) $pt === $httpCode)) {
                    $proxy['result']['drawProtocolType'] = 'Http / Http(s)';
                } elseif ($pt === 'ProxySocks') {
                    $proxy['result']['drawProtocolType'] = 'Socks5';
                } else {
                    $proxy['result']['drawProtocolType'] = '-';
                }
            }

            if ($proxy['result']['authentication']['isActive']) {
                $proxy['result']['drawProxy'] = @$proxy['result']['serverIp'].':'.@$proxy['result']['serverPort'].':'.@$proxy['result']['authentication']['userName'].':'.@$proxy['result']['authentication']['password'];
            } else {
                $proxy['result']['drawProxy'] = @$proxy['result']['serverIp'].':'.@$proxy['result']['serverPort'];
            }

            $airplaneMode = $service->getAirplaneModeSettings(@$proxy['result']['authToken']);
            if (@$airplaneMode['hasError']) {
                Logger::error('LOCALTONET_GET_AIRPLANE_MODE_SETTINGS_ERROR', ['order_id' => $orderId, 'errorCode' => @$airplaneMode['errorCode'], 'errors' => @$airplaneMode['errors']]);
            }
            $proxy['result']['airplaneMode'] = @$airplaneMode['result'];

            if (! isset($proxy['result']) || @$proxy['result']['id'] == 0) {
                return [];
            }

            if (empty($proxy['result']['serverPort'])) {
                return null;
            }

            return $proxy;
        });

        if ($localtonet_proxy_data === null) {
            Cache::forget('LOCALTONET_PR_DATA_'.$tunnelId);
            return false;
        }

        return $localtonet_proxy_data;
    }

    public function getProxyLocaltonet()
    {
        if (! $this->isLocaltonetLikeDelivery()) {
            return false;
        }
        $tunnelId = (int) ($this->getLocaltonetProxyId() ?? 0);
        if ($tunnelId <= 0) {
            return false;
        }

        $stored = $this->getStoredTunnelDetail($tunnelId);
        if ($stored !== null) {
            if (empty($stored['airplaneMode'])) {
                try {
                    $service = $this->resolveLocaltonetService();
                    $airplaneMode = $service->getAirplaneModeSettings($stored['authToken'] ?? '');
                    $stored['airplaneMode'] = @$airplaneMode['result'];
                } catch (\Throwable $e) {
                    // airplaneMode fetch failed
                }
            }
            return ['result' => $stored];
        }

        if ($this->isCanDeliveryType('LOCALTONETV4') && count($this->getAllLocaltonetProxyIds()) > 0) {
            try {
                $this->fetchAndPersistAllTunnelDetails();
                $this->refresh();
                $stored = $this->getStoredTunnelDetail($tunnelId);
                if ($stored !== null) {
                    return ['result' => $stored];
                }
            } catch (\Throwable $e) {
                // bulk fetch failed, fall through
            }
        }

        return $this->getLocaltonetTunnelDetailCached($tunnelId);
    }

    /**
     * DB'de (product_info) kayıtlı tünel detayını döner. Kayıt yoksa null.
     */
    public function getStoredTunnelDetail(int $tunnelId): ?array
    {
        $pi = $this->product_info ?? [];
        $details = $pi['localtonet_v4_tunnel_details'] ?? [];

        return isset($details[$tunnelId]) && is_array($details[$tunnelId]) ? $details[$tunnelId] : null;
    }

    /**
     * Tüm tünel detaylarını API'den çekip product_info['localtonet_v4_tunnel_details'] olarak DB'ye yazar.
     * getAllTunnelsV2() ile TEK API çağrısında tüm tünel bilgilerini alır.
     * Auth bilgileri product_info['localtonet_v4_auth_credentials'] varsa oradan okunur (API çağrısı yapılmaz).
     */
    public function fetchAndPersistAllTunnelDetails(): void
    {
        $ids = $this->getAllLocaltonetProxyIds();
        if (count($ids) === 0) {
            return;
        }

        $service = $this->resolveLocaltonetService();
        $listRes = $service->getAllTunnelsV2();

        if (! empty($listRes['hasError'])) {
            Logger::warning('FETCH_ALL_TUNNELS_V2_FAIL', [
                'order_id' => $this->id,
                'errors' => $listRes['errors'] ?? [],
            ]);

            return;
        }

        $tunnels = $listRes['tunnels'] ?? [];
        $idSet = array_flip(array_map('intval', $ids));

        $byId = [];
        foreach ($tunnels as $t) {
            if (! is_array($t)) {
                continue;
            }
            $tid = LocaltonetService::extractTunnelIdFromBulkV2Row($t);
            if ($tid !== null && isset($idSet[$tid])) {
                $byId[$tid] = $t;
            }
        }

        $storedAuth = $this->product_info['localtonet_v4_auth_credentials'] ?? [];
        $details = [];

        foreach ($ids as $tid) {
            $tid = (int) $tid;
            $row = $byId[$tid] ?? null;
            if ($row === null) {
                continue;
            }

            try {
                $detail = $this->buildTunnelDetailFromV2Row($row, $tid, $storedAuth, $service);
                if ($detail !== null) {
                    $details[$tid] = $detail;
                }
            } catch (\Throwable $e) {
                Logger::warning('BUILD_TUNNEL_DETAIL_FROM_V2_FAIL', [
                    'order_id' => $this->id,
                    'tunnel_id' => $tid,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $pi = is_array($this->product_info) ? $this->product_info : [];
        $pi['localtonet_v4_tunnel_details'] = $details;
        $this->product_info = $pi;
        $this->save();
    }

    /**
     * V2 tünel listesi satırından display için gereken detay yapısını oluşturur.
     */
    protected function buildTunnelDetailFromV2Row(array $row, int $tunnelId, array $storedAuth, LocaltonetService $service): ?array
    {
        $result = [];
        $result['id'] = $tunnelId;
        $result['serverIp'] = $row['serverIp'] ?? $row['ServerIp'] ?? '';
        $result['serverPort'] = $row['serverPort'] ?? $row['ServerPort'] ?? '';
        $result['authToken'] = $row['authToken'] ?? $row['AuthToken'] ?? '';
        $result['status'] = $row['status'] ?? $row['Status'] ?? 0;
        $result['title'] = $row['title'] ?? $row['Title'] ?? '';
        $result['protocolType'] = $row['protocolType'] ?? $row['ProtocolType'] ?? null;
        $result['isReserved'] = $row['isReserved'] ?? $row['IsReserved'] ?? false;
        $result['createDate'] = $row['createDate'] ?? $row['CreateDate'] ?? '';
        $result['ipRestrictions'] = $row['ipRestrictions'] ?? $row['IpRestrictions'] ?? [];

        $bwLimit = $row['bandwidthLimit'] ?? $row['BandwidthLimit'] ?? 0;
        if (is_numeric($bwLimit)) {
            $result['bandwidthLimit'] = $bwLimit > 0 ? $bwLimit : 'unlimited';
        } else {
            $result['bandwidthLimit'] = 0;
        }
        $bwUsage = $row['bandwidthUsage'] ?? $row['BandwidthUsage'] ?? 0;
        $result['bandwidthUsage'] = is_numeric($bwUsage) ? $bwUsage : 0;

        if ($result['bandwidthLimit'] == 0 || $result['bandwidthLimit'] == 'unlimited') {
            $result['bgBandwidthUsage'] = null;
        } else {
            $pct = ($result['bandwidthUsage'] / $result['bandwidthLimit']) * 100;
            $result['bgBandwidthUsage'] = $pct == 100 ? 'danger' : ($pct >= 60 ? 'warning' : null);
        }

        $auth = $storedAuth[$tunnelId] ?? null;
        if ($auth && is_array($auth)) {
            $result['authentication'] = [
                'isActive' => $auth['isActive'] ?? true,
                'userName' => $auth['userName'] ?? '',
                'password' => $auth['password'] ?? '',
            ];
        } else {
            try {
                $getAuth = $service->getAuthenticationDataByTunnelId($tunnelId);
                if (! $getAuth['hasError']) {
                    $authResult = $getAuth['result'] ?? null;
                    $result['authentication'] = [
                        'isActive' => $authResult['isActive'] ?? true,
                        'userName' => $authResult['userName'] ?? '',
                        'password' => $authResult['password'] ?? '',
                    ];
                }
            } catch (\Throwable $e) {
                // auth unavailable
            }
        }
        if (! isset($result['authentication'])) {
            $result['authentication'] = ['isActive' => true, 'userName' => '', 'password' => ''];
        }

        $isSocks = function_exists('localtonet_tunnel_result_is_socks') && localtonet_tunnel_result_is_socks($result);
        if ($isSocks) {
            $result['drawProtocolType'] = 'Socks5';
        } else {
            $pt = $result['protocolType'];
            $httpCode = (int) config('services.localtonet_v4.v2_protocol_http', 6);
            if ($pt === 'ProxyHttp' || (is_numeric($pt) && (int) $pt === $httpCode)) {
                $result['drawProtocolType'] = 'Http / Http(s)';
            } elseif ($pt === 'ProxySocks') {
                $result['drawProtocolType'] = 'Socks5';
            } else {
                $result['drawProtocolType'] = '-';
            }
        }

        if ($result['authentication']['isActive'] ?? false) {
            $result['drawProxy'] = $result['serverIp'] . ':' . $result['serverPort'] . ':' . $result['authentication']['userName'] . ':' . $result['authentication']['password'];
        } else {
            $result['drawProxy'] = $result['serverIp'] . ':' . $result['serverPort'];
        }

        try {
            $airplaneMode = $service->getAirplaneModeSettings($result['authToken'] ?? '');
            $result['airplaneMode'] = @$airplaneMode['result'];
        } catch (\Throwable $e) {
            $result['airplaneMode'] = null;
        }

        return $result;
    }

    /**
     * Tek bir tünelin detayını API'den çekip product_info['localtonet_v4_tunnel_details'][$tunnelId] olarak günceller.
     */
    public function refreshSingleTunnelInDb(int $tunnelId): void
    {
        $service = $this->resolveLocaltonetService();
        $detail = $this->fetchSingleTunnelDetailFromApi($service, $tunnelId);
        if ($detail === null) {
            return;
        }

        $this->refresh();
        $pi = is_array($this->product_info) ? $this->product_info : [];
        $details = $pi['localtonet_v4_tunnel_details'] ?? [];
        $details[$tunnelId] = $detail;
        $pi['localtonet_v4_tunnel_details'] = $details;
        $this->product_info = $pi;
        $this->save();
    }

    /**
     * Tek bir tünelin API detayını çeker ve normalize eder (getTunnelDetail + getAuth + airplaneMode).
     */
    protected function fetchSingleTunnelDetailFromApi(LocaltonetService $service, int $tunnelId): ?array
    {
        $proxy = $service->getTunnelDetail($tunnelId);
        if (@$proxy['hasError'] || ! isset($proxy['result']) || @$proxy['result']['id'] == 0) {
            return null;
        }

        $result = $proxy['result'];

        if (isset($result['bandwidthLimit']) && is_numeric($result['bandwidthLimit'])) {
            $result['bandwidthLimit'] = $result['bandwidthLimit'] > 0 ? $result['bandwidthLimit'] : 'unlimited';
        } else {
            $result['bandwidthLimit'] = 0;
        }

        $result['bandwidthUsage'] = isset($result['bandwidthUsage']) && is_numeric($result['bandwidthUsage'])
            ? $result['bandwidthUsage']
            : @$result['bandwidthUsage'];

        if ($result['bandwidthLimit'] == 0 || $result['bandwidthLimit'] == 'unlimited') {
            $result['bgBandwidthUsage'] = null;
        } else {
            $usagePercentage = ($result['bandwidthUsage'] / $result['bandwidthLimit']) * 100;
            $bg = null;
            if ($usagePercentage == 100) {
                $bg = 'danger';
            } elseif ($usagePercentage >= 60) {
                $bg = 'warning';
            }
            $result['bgBandwidthUsage'] = $bg;
        }

        $getAuthentication = $service->getAuthenticationDataByTunnelId($tunnelId);
        if (! $getAuthentication['hasError']) {
            $authResult = $getAuthentication['result'] ?? null;
            $result['authentication']['isActive'] = $authResult['isActive'] ?? null;
            $result['authentication']['userName'] = $authResult['userName'] ?? null;
            $result['authentication']['password'] = $authResult['password'] ?? null;
        }

        if (function_exists('localtonet_tunnel_result_is_socks') && localtonet_tunnel_result_is_socks($result)) {
            $result['drawProtocolType'] = 'Socks5';
        } else {
            $pt = $result['protocolType'] ?? $result['ProtocolType'] ?? null;
            $httpCode = (int) config('services.localtonet_v4.v2_protocol_http', 6);
            if ($pt === 'ProxyHttp' || (is_numeric($pt) && (int) $pt === $httpCode)) {
                $result['drawProtocolType'] = 'Http / Http(s)';
            } elseif ($pt === 'ProxySocks') {
                $result['drawProtocolType'] = 'Socks5';
            } else {
                $result['drawProtocolType'] = '-';
            }
        }

        if ($result['authentication']['isActive'] ?? false) {
            $result['drawProxy'] = @$result['serverIp'] . ':' . @$result['serverPort'] . ':' . @$result['authentication']['userName'] . ':' . @$result['authentication']['password'];
        } else {
            $result['drawProxy'] = @$result['serverIp'] . ':' . @$result['serverPort'];
        }

        $airplaneMode = $service->getAirplaneModeSettings(@$result['authToken']);
        $result['airplaneMode'] = @$airplaneMode['result'];

        return self::trimTunnelDetailFields($result);
    }

    protected static function trimTunnelDetailFields(array $detail): array
    {
        static $keep = [
            'id', 'serverIp', 'serverPort', 'authToken', 'status', 'protocolType',
            'isReserved', 'bandwidthLimit', 'bandwidthUsage', 'bgBandwidthUsage',
            'authentication', 'drawProtocolType', 'drawProxy', 'airplaneMode',
            'createDate', 'title', 'ipRestrictions',
        ];

        $trimmed = [];
        foreach ($keep as $k) {
            if (array_key_exists($k, $detail)) {
                $trimmed[$k] = $detail[$k];
            }
        }
        return $trimmed;
    }

    /**
     * DB'deki tünel detayının protokolünü inline günceller (API çağrısı yapmaz).
     */
    public function updateStoredTunnelProtocol(int $tunnelId, string $newProtocol): void
    {
        $this->refresh();
        $pi = is_array($this->product_info) ? $this->product_info : [];
        $details = $pi['localtonet_v4_tunnel_details'] ?? [];
        if (! isset($details[$tunnelId]) || ! is_array($details[$tunnelId])) {
            return;
        }

        $httpCode = (int) config('services.localtonet_v4.v2_protocol_http', 6);
        $socksCode = (int) config('services.localtonet_v4.v2_protocol_socks', 7);
        $isSocks = ($newProtocol === 'socks5');

        $details[$tunnelId]['protocolType'] = $isSocks ? $socksCode : $httpCode;
        $details[$tunnelId]['drawProtocolType'] = $isSocks ? 'Socks5' : 'Http / Http(s)';

        $ip = $details[$tunnelId]['serverIp'] ?? '';
        $port = $details[$tunnelId]['serverPort'] ?? '';
        $authActive = $details[$tunnelId]['authentication']['isActive'] ?? false;
        if ($authActive) {
            $user = $details[$tunnelId]['authentication']['userName'] ?? '';
            $pass = $details[$tunnelId]['authentication']['password'] ?? '';
            $details[$tunnelId]['drawProxy'] = $ip . ':' . $port . ':' . $user . ':' . $pass;
        } else {
            $details[$tunnelId]['drawProxy'] = $ip . ':' . $port;
        }

        $pi['localtonet_v4_tunnel_details'] = $details;
        $this->product_info = $pi;
        $this->save();
    }

    public function createProxyTitle()
    {
        if (App::environment('local')) {
            return "netlocal" . $this->id . " - " . @$this->product["name"] . "/" . @$this->activeDetail->price_data["duration"] . ' ' . convertDurationText(@$this->activeDetail->price_data["duration_unit"]);
        }
        return "T" . $this->id . " - " . @$this->product["name"] . "/" . @$this->activeDetail->price_data["duration"] . ' ' . convertDurationText(@$this->activeDetail->price_data["duration_unit"]);
    }

    public function getLocaltonetProxyId()
    {
        return isset($this?->product_info["proxy_id"]) ? $this->product_info["proxy_id"] : null;
    }

    /**
     * Onlarca tünel + port denemesi sync queue / tek web isteğinde 30s PHP limitini aşmasın diye.
     */
    public static function extendPhpRuntimeForLocaltonetDelivery(): void
    {
        $seconds = (int) config('services.localtonet_v4.delivery_max_execution_seconds', 7200);
        if ($seconds !== 0 && $seconds < 120) {
            $seconds = 120;
        }
        if ($seconds > 0) {
            @ini_set('max_execution_time', (string) $seconds);
        } else {
            @ini_set('max_execution_time', '0');
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit($seconds > 0 ? $seconds : 0);
        }
    }

    public function deliverLocaltonetItem()
    {
        if ((int) ini_get('memory_limit') > 0 && (int) ini_get('memory_limit') < 4096) {
            ini_set('memory_limit', '4096M');
        }

        $this->refresh();
        if (! $this->isLocaltonetLikeDelivery()) {
            return false;
        }

        $this->loadMissing(['product']);
        if ($this->product) {
            $this->maybeHealLocaltonetV4DeliveryStatus();
            $this->refresh();
        }

        if ($this->delivery_status === 'DELIVERED' && $this->getLocaltonetProxyId()) {
            return false;
        }

        if ($this->delivery_status === 'QUEUED') {
            $claimed = static::query()
                ->whereKey($this->id)
                ->where('delivery_status', 'QUEUED')
                ->update(['delivery_status' => 'BEING_DELIVERED']);
            if ($claimed === 0) {
                return false;
            }
            $this->refresh();
        }

        static::extendPhpRuntimeForLocaltonetDelivery();

        $service = $this->resolveLocaltonetService();
        $protocolType = 6; //default -> HTTP
        if ($this->activeDetail->additional_services){
            foreach ($this->activeDetail->additional_services as $extraService) {
                if ($extraService["value"] == "socks5") {
                    $protocolType = 7; //SOCKS5
                }
            }
        }

        if (! $this->product) {
            Logger::error('LOCALTONET_DELIVER_PRODUCT_MISSING', ['order_id' => $this->id]);

            return false;
        }

        $serverCode = $this->product->delivery_items['server_code'] ?? ($this->isCanDeliveryType('LOCALTONETV4') ? 'app' : 'tr520');

        if ($this->isCanDeliveryType('LOCALTONETV4')) {
            return $this->deliverLocaltonetV4Item($service, $protocolType, $serverCode);
        }

        /**  start::auth token */
        $nextAuthToken = $this->product->getNextAuthTokenForDelivery();
        if (!isset($nextAuthToken["token"])) {
            Logger::error("ORDER_APPROVE_ACTIVE_TOKEN_NOT_FOUND", ["order_id" => $this->id]);
            $this->status = "PENDING";
            $this->delivery_status = "NOT_DELIVERED";
            $this->delivery_error = "ORDER_APPROVE_ACTIVE_TOKEN_NOT_FOUND";
            $this->save();
//            throw new LocaltonetException("Aktif auth token bulunamadı.");
            return false;
        }
        /** end::auth token */

        $localServerIp = null;

        $serviceRes = $service->createProxyTunnel($protocolType, $serverCode, $nextAuthToken['token'], $localServerIp);
        if ($serviceRes && @$serviceRes["hasError"]) {
            Logger::error("ORDER_APPROVE_LOCALTONET_CREATE_PROXY_ERROR", ["order_id" => $this->id, "errorCode" => @$serviceRes["errorCode"], "errors" => @$serviceRes["errors"]]);
            $this->status = "PENDING";
            $this->delivery_status = "NOT_DELIVERED";
            $this->delivery_error = "ORDER_APPROVE_LOCALTONET_CREATE_PROXY_ERROR: " . (is_array(@$serviceRes["errors"]) ? implode(" ", @$serviceRes["errors"]) : @$serviceRes["errors"]);
            $this->save();
//            throw new LocaltonetException(@$serviceRes["errorCode"] . " " . (is_array(@$serviceRes["errors"]) ? implode(" ", @$serviceRes["errors"]) : @$serviceRes["errors"]));
            return false;
        }


        $proxyId = $serviceRes["result"]["id"] ?? null;
        $productInfo = [
            'proxy_id' => $proxyId,
        ];
        $this->product_info = $productInfo;
        $this->delivery_status = "DELIVERED";
        $this->status = "ACTIVE";
        $this->save();

        if ($proxyId) {
            Cache::forget('LOCALTONET_PR_DATA_' . $proxyId);
        }

        event(new LocaltonetProxyCreated(
            $this,
            $nextAuthToken['token'],
            []
        ));
        return true;
    }

    protected function rollbackLocaltonetV4Created(LocaltonetService $service, array $tunnelIds): void
    {
        $intIds = array_values(array_filter(array_map('intval', $tunnelIds)));
        if (count($intIds) === 0) {
            return;
        }
        $res = $service->bulkDeleteTunnelsV2($intIds);
        if (! empty($res['hasError'])) {
            Logger::warning('LOCALTONET_V4_ROLLBACK_BULK_DELETE_FAIL', [
                'order_id' => $this->id ?? null,
                'count' => count($intIds),
                'errors' => $res['errors'] ?? [],
            ]);
            foreach ($intIds as $tid) {
                $service->deleteTunnel($tid);
            }
        }
    }

    protected function deliverLocaltonetV4Item(LocaltonetService $service, int $protocolType, string $serverCode): bool
    {
        $di = $this->product->delivery_items ?? [];
        $count = (int) ($di['delivery_count'] ?? 1);
        if ($count < 1) {
            $count = 1;
        }
        if ($count > 1000) {
            $count = 1000;
        }

        $plan = $this->product->allocateLocaltonetV4DeliveryPlan($this, $count);
        if ($plan === null || count($plan) < $count) {
            Logger::error('LOCALTONET_V4_DELIVERY_PLAN_FAILED', [
                'order_id' => $this->id,
                'product_id' => $this->product->id,
                'count' => $count,
            ]);
            $this->rollbackLocaltonetV4Created($service, []);
            $this->status = 'PENDING';
            $this->delivery_status = 'NOT_DELIVERED';
            $this->delivery_error = 'LOCALTONET_V4_INSUFFICIENT_UNIQUE_POOL_IPS';
            $this->save();

            return false;
        }

        $bulkMin = (int) config('services.localtonet_v4.bulk_proxy_min_count', 10);
        if ($count >= $bulkMin) {
            return $this->deliverLocaltonetV4ItemBulk($service, $protocolType, $serverCode, $plan, $di, $count);
        }

        $createdIds = [];
        $snapshots = [];

        foreach ($plan as $i => $nextAuthToken) {
            if (! isset($nextAuthToken['token'])) {
                Logger::error('ORDER_APPROVE_ACTIVE_TOKEN_NOT_FOUND', ['order_id' => $this->id]);
                $this->rollbackLocaltonetV4Created($service, $createdIds);
                $this->status = 'PENDING';
                $this->delivery_status = 'NOT_DELIVERED';
                $this->delivery_error = 'ORDER_APPROVE_ACTIVE_TOKEN_NOT_FOUND';
                $this->save();

                return false;
            }

            $resolved = $this->v4ResolveLocalServerIpAndArgumentsForPlanRow($nextAuthToken, $di, $service, $serverCode);
            $localServerIp = $resolved['localServerIp'];
            $tunnelArguments = $resolved['tunnelArguments'];

            $serviceRes = $service->createProxyTunnel($protocolType, $serverCode, $nextAuthToken['token'], $localServerIp);
            if ($serviceRes && @$serviceRes['hasError']) {
                Logger::error('ORDER_APPROVE_LOCALTONET_CREATE_PROXY_ERROR', ['order_id' => $this->id, 'errorCode' => @$serviceRes['errorCode'], 'errors' => @$serviceRes['errors']]);
                $this->rollbackLocaltonetV4Created($service, $createdIds);
                $this->status = 'PENDING';
                $this->delivery_status = 'NOT_DELIVERED';
                $this->delivery_error = 'ORDER_APPROVE_LOCALTONET_CREATE_PROXY_ERROR: '.(is_array(@$serviceRes['errors']) ? implode(' ', @$serviceRes['errors']) : (string) (@$serviceRes['errors'] ?? ''));
                $this->save();

                return false;
            }

            $proxyId = $serviceRes['result']['id'] ?? null;
            if ($proxyId && $tunnelArguments !== null && $tunnelArguments !== '') {
                $argRes = $service->patchTunnelArguments($proxyId, $tunnelArguments);
                if ($argRes && @$argRes['hasError']) {
                    Logger::error('ORDER_APPROVE_LOCALTONET_PATCH_ARGUMENTS_ERROR', [
                        'order_id' => $this->id,
                        'tunnel_id' => $proxyId,
                        'errorCode' => @$argRes['errorCode'],
                        'errors' => @$argRes['errors'],
                    ]);
                    $service->deleteTunnel($proxyId);
                    $this->rollbackLocaltonetV4Created($service, $createdIds);
                    $this->status = 'PENDING';
                    $this->delivery_status = 'NOT_DELIVERED';
                    $this->delivery_error = 'ORDER_APPROVE_LOCALTONET_PATCH_ARGUMENTS_ERROR: '.(is_array(@$argRes['errors']) ? implode(' ', $argRes['errors']) : (string) (@$argRes['errors'] ?? ''));
                    $this->save();

                    return false;
                }
            }

            if ($proxyId) {
                $pp = LocaltonetService::allocateDistinctManualServerPorts(1);
                if (count($pp) === 1) {
                    $ap = $service->assignTunnelServerPortPreferV2((int) $proxyId, $pp[0]);
                    if (! empty($ap['hasError'])) {
                        Logger::warning('LOCALTONET_V4_SINGLE_CREATE_PREASSIGN_PORT', [
                            'order_id' => $this->id,
                            'tunnel_id' => $proxyId,
                            'errors' => $ap['errors'] ?? [],
                        ]);
                    }
                }
            }

            $createdIds[] = $proxyId;
            $snapshots[] = [
                'selected_ip' => $nextAuthToken['selected_ip'] ?? '',
            ];

            event(new LocaltonetProxyCreated(
                $this,
                $nextAuthToken['token'],
                [],
                (int) $proxyId,
                (int) $i + 1,
                $count
            ));
        }

        $firstSnap = $snapshots[0] ?? [];
        $this->product_info = [
            'proxy_id' => $createdIds[0] ?? null,
            'localtonet_v4_proxy_ids' => $createdIds,
            'localtonet_v4_snapshots' => $snapshots,
            'localtonet_v4_snapshot' => $firstSnap,
        ];
        $this->delivery_status = 'DELIVERED';
        $this->status = 'ACTIVE';
        $this->save();

        try {
            \App\Models\ProxyLog::logBulk($this, $createdIds, 'DELIVER');
        } catch (\Throwable $e) {
            Logger::warning('PROXY_LOG_DELIVER_FAIL', ['order_id' => $this->id, 'msg' => $e->getMessage()]);
        }

        return true;
    }

    /**
     * @return array{localServerIp: string, tunnelArguments: string|null}
     */
    protected function v4ResolveLocalServerIpAndArgumentsForPlanRow(array $nextAuthToken, array $di, LocaltonetService $service, string $serverCode): array
    {
        $localServerIp = null;
        $picked = trim((string) ($nextAuthToken['selected_ip'] ?? ''));
        if ($picked !== '' && filter_var($picked, FILTER_VALIDATE_IP)) {
            $localServerIp = $picked;
        }
        if ($localServerIp === null) {
            $configured = isset($di['local_server_ip']) ? trim((string) $di['local_server_ip']) : '';
            if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_IP)) {
                $localServerIp = $configured;
            }
        }
        if ($localServerIp === null) {
            $localServerIp = $service->resolveLocalServerIpForServerCode($serverCode);
        }
        if ($localServerIp === null) {
            $localServerIp = '127.0.0.1';
        }

        $tunnelArguments = null;
        if ($localServerIp !== null
            && $localServerIp !== ''
            && filter_var($localServerIp, FILTER_VALIDATE_IP)
            && ! in_array($localServerIp, ['127.0.0.1', '::1'], true)) {
            $net = trim((string) (data_get($di, 'v4_tunnel_net')
                ?: config('services.localtonet_v4.tunnel_net_interface', 'Ethernet0')));
            if ($net === '' || ! preg_match('/^[A-Za-z0-9_.-]+$/', $net)) {
                $net = 'Ethernet0';
            }
            $tunnelArguments = '--net '.$net.' --ip '.$localServerIp;
        }

        return ['localServerIp' => $localServerIp, 'tunnelArguments' => $tunnelArguments];
    }

    /**
     * @param  list<array<string, mixed>>  $plan
     */
    protected function v4BulkBandwidthLimitData(): ?array
    {
        $bw = $this->product_data['delivery_items']['bandwidth_limit'] ?? null;
        if (! is_array($bw) || ! isset($bw['data_size'], $bw['data_size_type'])) {
            return null;
        }

        return [
            'dataSize' => $bw['data_size'],
            'dataSizeType' => (int) $bw['data_size_type'],
        ];
    }

    /**
     * Toplu v2 isteğinde kullanılacak bitiş tarihi (sipariş end_date / test ürünü ile uyumlu).
     */
    protected function v4BulkExpirationDateIso(): ?string
    {
        if (! $this->end_date) {
            return null;
        }
        try {
            $this->loadMissing(['activeDetail.price']);
            $endDate = $this->end_date->format('Y-m-d').' '.date('H:i');
            if ($this->activeDetail?->price?->is_test_product) {
                $endDate = Carbon::now()->addHours(2)->format('Y-m-d H:i');
            }

            return Carbon::createFromFormat('Y-m-d H:i', $endDate)->format('Y-m-d\TH:i:s.v\Z');
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_V4_BULK_EXPIRATION_PARSE', [
                'order_id' => $this->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Toplu teslimatta tünel başlığı (updateTitle ile aynı 30 karakter sınırı).
     */
    protected function v4BulkTunnelTitle(int $ordinal1Based, int $totalCount): string
    {
        $base = $this->createProxyTitle();
        if ($totalCount > 1 && $ordinal1Based >= 1) {
            $base .= ' #'.$ordinal1Based;
        }

        return mb_substr($base, 0, 30);
    }

    /**
     * @param  list<array<string, mixed>>  $plan
     */
    protected function deliverLocaltonetV4ItemBulk(LocaltonetService $service, int $protocolType, string $serverCode, array $plan, array $di, int $count): bool
    {
        $chunk = max(1, min(100, (int) config('services.localtonet_v4.bulk_proxy_chunk_size', 100)));
        $bwData = $this->v4BulkBandwidthLimitData();
        $createdIds = [];
        $snapshots = [];
        $expirationIso = $this->v4BulkExpirationDateIso();

        $preassignedPorts = LocaltonetService::allocateDistinctManualServerPorts($count);
        if (count($preassignedPorts) !== $count) {
            Logger::error('LOCALTONET_V4_PREASSIGN_PORTS_FAILED', [
                'order_id' => $this->id,
                'expected' => $count,
                'got' => count($preassignedPorts),
            ]);
            $this->rollbackLocaltonetV4Created($service, []);
            $this->status = 'PENDING';
            $this->delivery_status = 'NOT_DELIVERED';
            $this->delivery_error = 'LOCALTONET_V4_PREASSIGN_PORTS_FAILED';
            $this->save();

            return false;
        }

        $offset = 0;
        while ($offset < $count) {
            $slice = array_slice($plan, $offset, $chunk);
            $sliceCount = count($slice);
            if ($sliceCount === 0) {
                break;
            }

            $items = [];
            $chunkMeta = [];
            foreach ($slice as $relIdx => $nextAuthToken) {
                if (! isset($nextAuthToken['token'])) {
                    Logger::error('ORDER_APPROVE_ACTIVE_TOKEN_NOT_FOUND', ['order_id' => $this->id]);
                    $this->rollbackLocaltonetV4Created($service, $createdIds);
                    $this->status = 'PENDING';
                    $this->delivery_status = 'NOT_DELIVERED';
                    $this->delivery_error = 'ORDER_APPROVE_ACTIVE_TOKEN_NOT_FOUND';
                    $this->save();

                    return false;
                }
                $globalIdx = $offset + (int) $relIdx;
                $globalOrdinal = $globalIdx + 1;
                $resolved = $this->v4ResolveLocalServerIpAndArgumentsForPlanRow($nextAuthToken, $di, $service, $serverCode);
                $title = $this->v4BulkTunnelTitle($globalOrdinal, $count);
                $authUser = Str::random(8);
                $authPass = Str::random(12);
                $serverPort = $preassignedPorts[$globalIdx];
                $chunkMeta[] = [
                    'token' => $nextAuthToken['token'],
                    'localServerIp' => $resolved['localServerIp'],
                    'tunnelArguments' => $resolved['tunnelArguments'],
                    'authUser' => $authUser,
                    'authPass' => $authPass,
                    'title' => $title,
                    'serverPort' => $serverPort,
                ];
                $items[] = LocaltonetService::buildV4BulkProxyItem(
                    $protocolType,
                    $serverCode,
                    $nextAuthToken['token'],
                    $resolved['localServerIp'],
                    $resolved['tunnelArguments'],
                    'order-'.$this->id.'-'.$globalIdx,
                    $bwData,
                    $title,
                    $authUser,
                    $authPass,
                    $expirationIso,
                    $serverPort
                );
            }

            $bulkRes = $service->createProxyTunnelsBulkV2WithDetail($items);
            if (! empty($bulkRes['hasError'])) {
                $errMsg = isset($bulkRes['errors']) && is_array($bulkRes['errors'])
                    ? implode(' ', $bulkRes['errors'])
                    : 'LOCALTONET_V4_BULK_CREATE_ERROR';
                Logger::error('ORDER_APPROVE_LOCALTONET_BULK_PROXY_ERROR', [
                    'order_id' => $this->id,
                    'offset' => $offset,
                    'chunk' => $sliceCount,
                    'errors' => $bulkRes['errors'] ?? [],
                ]);
                $this->rollbackLocaltonetV4Created($service, $createdIds);
                $this->status = 'PENDING';
                $this->delivery_status = 'NOT_DELIVERED';
                $this->delivery_error = 'ORDER_APPROVE_LOCALTONET_BULK_PROXY_ERROR: '.$errMsg;
                $this->save();

                return false;
            }

            $tunnelIds = $bulkRes['tunnelIds'] ?? [];
            if (count($tunnelIds) !== $sliceCount) {
                Logger::error('ORDER_APPROVE_LOCALTONET_BULK_PROXY_ID_MISMATCH', [
                    'order_id' => $this->id,
                    'expected' => $sliceCount,
                    'got' => count($tunnelIds),
                ]);
                foreach ($tunnelIds as $tid) {
                    if ($tid) {
                        $service->deleteTunnel((int) $tid);
                    }
                }
                $this->rollbackLocaltonetV4Created($service, $createdIds);
                $this->status = 'PENDING';
                $this->delivery_status = 'NOT_DELIVERED';
                $this->delivery_error = 'ORDER_APPROVE_LOCALTONET_BULK_PROXY_ID_MISMATCH';
                $this->save();

                return false;
            }

            $protocolBulkItems = [];
            foreach ($tunnelIds as $tid) {
                $protocolBulkItems[] = [
                    'tunnelId' => (int) $tid,
                    'protocolType' => $protocolType,
                ];
            }
            $protoRes = $service->patchTunnelsBulkProtocolType($protocolBulkItems);
            if (! empty($protoRes['hasError'])) {
                Logger::warning('ORDER_APPROVE_LOCALTONET_BULK_PROTOCOL_RETRY_SINGLE', [
                    'order_id' => $this->id,
                    'offset' => $offset,
                    'errors' => $protoRes['errors'] ?? [],
                ]);
                $protoFallbackOk = true;
                foreach ($tunnelIds as $tid) {
                    if (! $tid) {
                        continue;
                    }
                    $one = $service->patchTunnelProtocolType((int) $tid, $protocolType);
                    if (! empty($one['hasError'])) {
                        $protoFallbackOk = false;
                        Logger::error('ORDER_APPROVE_LOCALTONET_PROTOCOL_SINGLE_FAILED', [
                            'order_id' => $this->id,
                            'tunnel_id' => $tid,
                            'errors' => $one['errors'] ?? [],
                        ]);
                    }
                }
                if (! $protoFallbackOk) {
                    $errMsg = isset($protoRes['errors']) && is_array($protoRes['errors'])
                        ? implode(' ', $protoRes['errors'])
                        : 'LOCALTONET_V4_BULK_PROTOCOL_ERROR';
                    foreach ($tunnelIds as $tid) {
                        if ($tid) {
                            $service->deleteTunnel((int) $tid);
                        }
                    }
                    $this->rollbackLocaltonetV4Created($service, $createdIds);
                    $this->status = 'PENDING';
                    $this->delivery_status = 'NOT_DELIVERED';
                    $this->delivery_error = 'ORDER_APPROVE_LOCALTONET_BULK_PROTOCOL_ERROR: '.$errMsg;
                    $this->save();

                    return false;
                }
            }

            $authCredentials = is_array($this->product_info) ? ($this->product_info['localtonet_v4_auth_credentials'] ?? []) : [];

            foreach ($slice as $relIdx => $nextAuthToken) {
                $proxyId = $tunnelIds[(int) $relIdx];
                $meta = $chunkMeta[(int) $relIdx];

                $createdIds[] = $proxyId;
                $snapshots[] = [
                    'selected_ip' => $nextAuthToken['selected_ip'] ?? '',
                ];

                $authCredentials[(int) $proxyId] = [
                    'isActive' => true,
                    'userName' => $meta['authUser'],
                    'password' => $meta['authPass'],
                ];
            }

            $offset += $sliceCount;

            $firstSnap = $snapshots[0] ?? [];
            $pi = is_array($this->product_info) ? $this->product_info : [];
            $pi['proxy_id'] = $createdIds[0] ?? null;
            $pi['localtonet_v4_proxy_ids'] = $createdIds;
            $pi['localtonet_v4_snapshots'] = $snapshots;
            $pi['localtonet_v4_snapshot'] = $firstSnap;
            $pi['localtonet_v4_auth_credentials'] = $authCredentials;
            $this->product_info = $pi;
            $this->save();

            Logger::info('LOCALTONET_V4_CHUNK_IDS_SAVED', [
                'order_id' => $this->id,
                'chunk_count' => $sliceCount,
                'total_ids' => count($createdIds),
            ]);
        }

        $this->delivery_status = 'DELIVERED';
        $this->status = 'ACTIVE';
        $this->save();

        try {
            \App\Models\ProxyLog::logBulk($this, $createdIds, 'DELIVER');
        } catch (\Throwable $e) {
            Logger::warning('PROXY_LOG_DELIVER_FAIL', ['order_id' => $this->id, 'msg' => $e->getMessage()]);
        }

        foreach ($createdIds as $stId) {
            if (! $stId) {
                continue;
            }
            $st = $service->startTunnel((int) $stId);
            if (! empty($st['hasError'])) {
                Logger::warning('LOCALTONET_V4_POST_DELIVER_START_FAIL', [
                    'order_id' => $this->id,
                    'tunnel_id' => $stId,
                    'errors' => $st['errors'] ?? [],
                ]);
            }
        }

        try {
            $this->fetchAndPersistAllTunnelDetails();
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_V4_POST_DELIVER_DETAIL_FETCH_FAIL', [
                'order_id' => $this->id,
                'message' => $e->getMessage(),
            ]);
        }

        foreach ($createdIds as $cid) {
            if (! $cid) {
                continue;
            }
            $globalOrdinal = array_search($cid, $createdIds, true);
            $globalOrdinal = $globalOrdinal !== false ? $globalOrdinal + 1 : 0;
            $meta = $authCredentials[(int) $cid] ?? [];
            event(new LocaltonetProxyCreated(
                $this,
                '',
                [],
                (int) $cid,
                $globalOrdinal,
                $count,
                $meta['userName'] ?? '',
                $meta['password'] ?? ''
            ));
        }

        return true;
    }

    public function localtonetApprove()
    {
        $this->status = 'ACTIVE';
        $this->delivery_status = 'QUEUED';
        $this->saveQuietly();

        $orderId = $this->id;
        $dispatchBg = static function () use ($orderId): void {
            \App\Http\Controllers\Admin\OrderController::dispatchDeliveryInBackground($orderId);
        };
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($dispatchBg);
        } else {
            $dispatchBg();
        }

        return true;
    }

    /* ================================================================
     *  LOCALTONET_ROTATING  –  Shared Proxy Client delivery
     * ================================================================ */

    public function localtonetRotatingApprove(): bool
    {
        $this->loadMissing('product');
        $product = $this->product;
        if (! $product) {
            Logger::error('LR_APPROVE_PRODUCT_MISSING', ['order_id' => $this->id]);
            return false;
        }

        $di = $product->delivery_items ?? [];
        $poolId = (int) ($di['lr_pool_id'] ?? 0);
        $pool = \App\Models\LocaltonetRotatingPool::find($poolId);
        if (! $pool || ! $pool->api_key) {
            Logger::error('LR_APPROVE_POOL_NOT_FOUND', ['order_id' => $this->id, 'pool_id' => $poolId]);
            $this->update([
                'status' => 'PENDING',
                'delivery_status' => 'NOT_DELIVERED',
                'delivery_error' => 'LR_POOL_NOT_FOUND_OR_NO_API_KEY',
            ]);
            return false;
        }

        $tunnelIds = $pool->tunnel_ids ?? [];
        if (count($tunnelIds) === 0) {
            Logger::error('LR_APPROVE_POOL_EMPTY', ['order_id' => $this->id, 'pool_id' => $poolId]);
            $this->update([
                'status' => 'PENDING',
                'delivery_status' => 'NOT_DELIVERED',
                'delivery_error' => 'LR_POOL_HAS_NO_TUNNEL_IDS',
            ]);
            return false;
        }

        $deliveryCount = max(1, (int) ($di['delivery_count'] ?? 1));
        $host = trim((string) ($di['host'] ?? ''));
        $port = isset($di['port']) ? (int) $di['port'] : null;

        $quotaDataSize = $di['quota']['data_size'] ?? null;
        $quotaDataSizeType = (int) ($di['quota']['data_size_type'] ?? 4);
        $enableBandwidth = $pool->type === 'quota'
            && $quotaDataSize !== null && $quotaDataSize !== '' && (float) $quotaDataSize > 0;
        $bandwidthRawValue = $enableBandwidth ? max(1, (int) $quotaDataSize) : 0;
        $dataSizeTypeEnum = $this->dataSizeTypeToApiEnum($quotaDataSizeType);

        $service = new LocaltonetService(null, $pool->api_key);

        $createdClients = [];
        $username = Str::random(8);
        $password = Str::random(12);

        for ($i = 0; $i < $deliveryCount; $i++) {
            $tunnelId = $tunnelIds[$i % count($tunnelIds)];

            $payload = [
                'username' => $username,
                'password' => $password,
                'description' => 'Order #' . $this->id . ' - ' . ($product->name ?? ''),
                'enableBandwidthLimit' => $enableBandwidth,
                'dataSizeType' => $enableBandwidth ? $dataSizeTypeEnum : 1073741824,
                'bandwidthLimit' => $enableBandwidth ? $bandwidthRawValue : 0,
                'enableExpirationDate' => $this->end_date !== null,
                'expirationDate' => $this->end_date ? $this->end_date->format('Y-m-d\TH:i:s.v\Z') : null,
                'enableThreadLimit' => true,
                'threadLimit' => 50,
            ];

            $result = $service->createSharedProxyClient((int) $tunnelId, $payload);

            if (! empty($result['hasError'])) {
                Logger::error('LR_CREATE_SHARED_CLIENT_ERROR', [
                    'order_id' => $this->id,
                    'tunnel_id' => $tunnelId,
                    'errors' => $result['errors'] ?? [],
                ]);

                foreach ($createdClients as $cc) {
                    $service->deleteSharedProxyClient((int) $cc['tunnel_id'], $cc['client_id']);
                }

                $this->update([
                    'status' => 'PENDING',
                    'delivery_status' => 'NOT_DELIVERED',
                    'delivery_error' => 'LR_CREATE_SHARED_CLIENT_ERROR: ' . json_encode($result['errors'] ?? []),
                ]);
                return false;
            }

            $clientData = $result['result'] ?? [];
            $clientId = $clientData['id'] ?? $clientData['Id'] ?? null;

            $createdClients[] = [
                'tunnel_id' => (int) $tunnelId,
                'client_id' => $clientId,
            ];
        }

        $this->product_info = [
            'lr_pool_id' => $poolId,
            'lr_clients' => $createdClients,
            'lr_host' => $host,
            'lr_port' => $port,
            'lr_username' => $username,
            'lr_password' => $password,
        ];
        $this->delivery_status = 'DELIVERED';
        $this->status = 'ACTIVE';
        $this->start_date = now();
        if ($this->end_date === null && $product->prices()->first()) {
            $price = $product->prices()->first();
            if ($price && $price->duration && $price->duration_unit !== 'ONE_TIME') {
                $this->end_date = now()->addDays((int) $price->duration);
            }
        }
        $this->save();

        Logger::info('LR_DELIVERY_OK', [
            'order_id' => $this->id,
            'pool_id' => $poolId,
            'client_count' => count($createdClients),
        ]);

        return true;
    }

    public function localtonetRotatingRevokeApproval(): bool
    {
        $pi = $this->product_info ?? [];
        $clients = $pi['lr_clients'] ?? [];
        $poolId = (int) ($pi['lr_pool_id'] ?? 0);

        if (count($clients) === 0) {
            $this->update([
                'product_info' => null,
                'status' => 'CANCELLED',
                'delivery_status' => 'NOT_DELIVERED',
            ]);
            return true;
        }

        $pool = \App\Models\LocaltonetRotatingPool::find($poolId);
        if (! $pool || ! $pool->api_key) {
            Logger::error('LR_REVOKE_POOL_NOT_FOUND', ['order_id' => $this->id, 'pool_id' => $poolId]);
            $this->update([
                'product_info' => null,
                'status' => 'CANCELLED',
                'delivery_status' => 'NOT_DELIVERED',
            ]);
            return true;
        }

        $service = new LocaltonetService(null, $pool->api_key);

        foreach ($clients as $cc) {
            $tunnelId = (int) ($cc['tunnel_id'] ?? 0);
            $clientId = $cc['client_id'] ?? '';
            if ($tunnelId <= 0 || $clientId === '') continue;

            $deleteRes = $service->deleteSharedProxyClient($tunnelId, $clientId);
            if (! empty($deleteRes['hasError'])) {
                Logger::error('LR_DELETE_SHARED_CLIENT_ERROR', [
                    'order_id' => $this->id,
                    'tunnel_id' => $tunnelId,
                    'client_id' => $clientId,
                    'errors' => $deleteRes['errors'] ?? [],
                ]);
            }
        }

        $this->update([
            'product_info' => null,
            'status' => 'CANCELLED',
            'delivery_status' => 'NOT_DELIVERED',
        ]);

        Logger::info('LR_REVOKE_OK', ['order_id' => $this->id, 'client_count' => count($clients)]);

        return true;
    }

    /**
     * Form'daki data_size_type (1=Byte,2=KB,3=MB,4=GB,5=TB) -> Localtonet API enum değeri.
     * API: 1=Bytes, 1024=KB, 1048576=MB, 1073741824=GB, 1099511627776=TB
     */
    protected function dataSizeTypeToApiEnum(int $formType): int
    {
        return match ($formType) {
            1 => 1,
            2 => 1024,
            3 => 1048576,
            4 => 1073741824,
            5 => 1099511627776,
            default => 1073741824,
        };
    }

    public function localtonetRevokeApproval()
    {
        $tunnelIds = $this->getAllLocaltonetProxyIds();

        if (count($tunnelIds) > 0) {
            try {
                \App\Models\ProxyLog::logBulk($this, $tunnelIds, 'REVOKE');
            } catch (\Throwable $e) {
                Logger::warning('PROXY_LOG_REVOKE_FAIL', ['order_id' => $this->id, 'msg' => $e->getMessage()]);
            }
        }

        if (count($tunnelIds) === 0) {
            $productInfo = $this->product_info ?? [];
            $productInfo['proxy_id'] = null;
            unset($productInfo['localtonet_v4_snapshot'], $productInfo['localtonet_v4_proxy_ids'], $productInfo['localtonet_v4_snapshots'], $productInfo['localtonet_v4_tunnel_details'], $productInfo['localtonet_v4_auth_credentials']);
            $this->update([
                'product_info' => $productInfo,
                'status' => 'CANCELLED',
                'delivery_status' => 'NOT_DELIVERED',
            ]);
            return true;
        }

        $service = $this->resolveLocaltonetService();

        if (! $this->isCanDeliveryType('LOCALTONETV4')) {
            $firstId = $tunnelIds[0];
            $proxy = $service->getTunnelDetail($firstId);
            if ($proxy && ! @$proxy['hasError'] && isset($proxy['result']) && @$proxy['result']['id'] != 0) {
                $auth_token = $proxy['result']['authToken'];

                if ($this->product && $auth_token) {
                    $tokenPool = $this->product?->getTokenPool();
                    if ($tokenPool) {
                        $old_tokens = $tokenPool->tokens;
                        $old_tokens[] = $auth_token;
                        $tokenPool->tokens = $old_tokens;
                        $tokenPool->save();
                    }
                }
            }

            foreach ($tunnelIds as $tid) {
                $deleteTunnel = $service->deleteTunnel($tid);
                if (@$deleteTunnel['hasError']) {
                    Logger::error('LOCALTONET_DELETE_TUNNEL_ERROR_REVOKE_APPROVAL', ['order_id' => $this->id, 'tunnel_id' => $tid, 'errorCode' => @$deleteTunnel['errorCode'], 'errors' => @$deleteTunnel['errors'], 'response' => $deleteTunnel]);
                    return false;
                }
            }
        } else {
            $intIds = array_map('intval', $tunnelIds);

            $stopRes = $service->bulkStopTunnelsV2($intIds);
            if (! empty($stopRes['hasError'])) {
                Logger::warning('LOCALTONET_BULK_STOP_REVOKE_FAIL', [
                    'order_id' => $this->id,
                    'errors' => $stopRes['errors'] ?? [],
                ]);
            }

            $deleteRes = $service->bulkDeleteTunnelsV2($intIds);
            if (! empty($deleteRes['hasError'])) {
                Logger::error('LOCALTONET_BULK_DELETE_REVOKE_FAIL', [
                    'order_id' => $this->id,
                    'errors' => $deleteRes['errors'] ?? [],
                ]);
                return false;
            }

            Logger::info('LOCALTONET_BULK_DELETE_REVOKE_OK', [
                'order_id' => $this->id,
                'count' => count($intIds),
            ]);
        }

        $productInfo = $this->product_info ?? [];
        $productInfo['proxy_id'] = null;
        unset($productInfo['localtonet_v4_snapshot'], $productInfo['localtonet_v4_proxy_ids'], $productInfo['localtonet_v4_snapshots'], $productInfo['localtonet_v4_tunnel_details'], $productInfo['localtonet_v4_auth_credentials']);
        $update = $this->update([
            'product_info' => $productInfo,
            'status' => 'CANCELLED',
            'delivery_status' => 'NOT_DELIVERED',
        ]);

        if (! $update) {
            return false;
        }

        return true;
    }

    public function localtonetSetExpirationDate($endDate)
    {
        $service = $this->resolveLocaltonetService();

        $formatted = $endDate->setTime(23, 59, 0)->format('Y-m-d\TH:i:s.v\Z');
        foreach ($this->getAllLocaltonetProxyIds() as $tunnelId) {
            $setExpirationDate = $service->setExpirationDateForTunnel($tunnelId, $formatted);
            if (@$setExpirationDate['hasError']) {
                Logger::error('LOCALTONET_SET_EXPIRATION_DATE_ERROR', ['order_id' => $this->id, 'tunnelId' => $tunnelId, 'errorCode' => @$setExpirationDate['errorCode'], 'errors' => @$setExpirationDate['errors']]);
                throw new LocaltonetException('Proxy bitiş tarihi localtonet tarafında düzenlenemedi. TunnelId: '.$tunnelId);
            }
        }

        return true;
    }

    public function changeProxyTypeFromSocksToHttp(): bool
    {
        $idsToForget = $this->getAllLocaltonetProxyIds();
        $this->revokeApproval();
//        $deleteProxy = $this->deleteLocaltonetProxy();
//        if (!$deleteProxy) return false;

        $additionalServicesCollection = collect($this->activeDetail->additional_services);
        $additionalServices = $additionalServicesCollection->filter(function ($service) {
            return $service['service_type'] != 'protocol_select';
        });
        $additionalServices[] = getAdditionalServices($this->product, "protocol_secimi", "http");

        $this->activeDetail->update([
            "additional_services" => $additionalServices
        ]);

        foreach ($idsToForget as $tid) {
            Cache::forget('LOCALTONET_PR_DATA_'.$tid);
        }
        $this->deliverLocaltonetItem();
        return true;
    }

    public function changeProxyTypeFromHttpToSocks(): bool
    {
        $idsToForget = $this->getAllLocaltonetProxyIds();
        $this->revokeApproval();
//        $deleteProxy = $this->deleteLocaltonetProxy();
//        if (!$deleteProxy) return false;

        $additionalServicesCollection = collect($this->activeDetail->additional_services);
        $additionalServices = $additionalServicesCollection->filter(function ($service){
            return $service['service_type'] != 'protocol_select';
        });
        $newAdditionalService = getAdditionalServices($this->product, "protocol_secimi", "socks5");
//        $newAdditionalService["price"] = 0.00;
//        $newAdditionalService["price_without_vat"] = 0.00;
        $additionalServices[] = $newAdditionalService;
        $this->activeDetail->update([
            "additional_services" => $additionalServices
        ]);

        //$this->activeDetail->price_data price data güncellenebilir ama httpyi kaçtan aldı verisi yok suan dusemiyorum pricedan düşüp yeni price ekleyemiyoruz.
        foreach ($idsToForget as $tid) {
            Cache::forget('LOCALTONET_PR_DATA_'.$tid);
        }
        $this->deliverLocaltonetItem();
        return true;
    }

    public function changeDevice()
    {


        $proxy_info = $this->getLocaltonetTunnelDetail();
        $this->deleteLocaltonetProxy();

//dd($proxy_info);

        $limit = $proxy_info['bandwidthLimit'] ?? 0;
        $usage = $proxy_info['bandwidthUsage'] ?? 0;

        if ($limit > 0){

            $limit = intval($limit)-intval($usage);

            $old_product_data = $this->product_data;

            $old_product_data['delivery_items']['bandwidth_limit'] = [
                'data_size' => intval($limit / 1048576),
                'data_size_type' => 3,
            ];

            $this->product_data = $old_product_data;

            $this->save();
        }
        $this->revokeApproval();
        $this->deliverLocaltonetItem();

        return true;
    }

    public function deleteLocaltonetProxy()
    {
        $service = $this->resolveLocaltonetService();

        $ids = $this->getAllLocaltonetProxyIds();
        if (count($ids) === 0) {
            return false;
        }
        foreach ($ids as $tid) {
            $deleteTunnel = $service->deleteTunnel($tid);
            if (@$deleteTunnel['hasError']) {
                Logger::error('LOCALTONET_DELETE_TUNNEL_ERROR_REVOKE_APPROVAL', ['order_id' => $this->id, 'tunnel_id' => $tid, 'errorCode' => @$deleteTunnel['errorCode'], 'errors' => @$deleteTunnel['errors'], 'response' => $deleteTunnel]);

                return false;
            }
        }

        return true;
    }

    public function getLocaltonetIpHistory()
    {
        $proxyId = $this->getLocaltonetProxyId();
        if (! $proxyId) {
            return [];
        }

        $tunnel = $this->getLocaltonetTunnelDetail();
        if (! $tunnel || empty($tunnel['authToken'])) {
            Logger::error('LOCALTONET_GET_IP_HISTORY_TUNNEL_OR_TOKEN_MISSING', ['order_id' => $this->id]);

            return [];
        }

        $service = $this->resolveLocaltonetService();
        $ipHistory = [];

        $getIpHistoryResult = Cache::remember('LOCALTONET_PR_IP_HISTORY_'.$proxyId, 60, function () use ($service, $tunnel) {
            return $service->getIpHistoryByAuthToken($tunnel['authToken']);
        });

        if (@$getIpHistoryResult['hasError']) {
            Logger::error('LOCALTONET_GET_IP_HISTORY_BY_AUTH_TOKEN_ERROR', ['order_id' => $this->id, 'errorCode' => @$getIpHistoryResult['errorCode'], 'errors' => @$getIpHistoryResult['errors']]);

            return [];
        }

        $getIpHistoryResult = $getIpHistoryResult['result'] ?? null;
        $ipHistory = is_array($getIpHistoryResult) ? $getIpHistoryResult : [];

        if (! empty($ipHistory)) {
            $orderPlacedAt = Carbon::parse($this->created_at);
            $tunnelCreatedAt = Carbon::parse(@$tunnel['createDate']);
            $filterSince = $orderPlacedAt->gt($tunnelCreatedAt) ? $orderPlacedAt : $tunnelCreatedAt;

            foreach ($ipHistory as $index => $history) {
                if (empty($history['date'])) {
                    unset($ipHistory[$index]);

                    continue;
                }

                try {
                    $historyDate = Carbon::parse($history['date']);
                } catch (\Throwable $e) {
                    unset($ipHistory[$index]);

                    continue;
                }

                if ($historyDate->lt($filterSince)) {
                    unset($ipHistory[$index]);

                    continue;
                }

                $ipHistory[$index]['date'] = formatDateTimeInAppTimezone($history['date']);
            }
        }

        return array_values($ipHistory);
    }

    public function getLocaltonetTunnelDetail()
    {
        $proxyId = $this->getLocaltonetProxyId();
        if (!$proxyId) return false;
        $service = $this->resolveLocaltonetService();

        $response = $service->getTunnelDetail($proxyId);
        if (@$response["hasError"] || !isset($response["result"]) || @$response["result"]["id"] == 0) {
            Logger::error("LOCALTONET_GET_TUNNEL_DETAIL_ERROR", ["order_id" => $this->id, "errorCode" => @$response["errorCode"], "errors" => @$response["errors"]]);
            return false;
        }
        return $response["result"] ?? false;
    }

    public function renewAuthToken()
    {

    }
}
