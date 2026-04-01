<?php

namespace App\Services;

use App\Library\Logger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LocaltonetService
{
    private string $apiKey;

    private string $apiUrl;

    public function __construct(?string $apiUrl = null, ?string $apiKey = null)
    {
        $defaultUrl = 'https://localtonet.com/api/';
        $this->apiUrl = rtrim($apiUrl !== null && $apiUrl !== '' ? $apiUrl : $defaultUrl, '/').'/';
        $this->apiKey = $apiKey !== null && $apiKey !== ''
            ? $apiKey
            : 'VomWzxPNOaijYnHURurDs9MvF768kgfEdXSpcKCLBhb2q';
    }

    private function authTokensCacheKey(): string
    {
        return 'localtonet_auth_tokens_'.md5($this->apiUrl.'|'.$this->apiKey);
    }

    /**
     * Guzzle verify seçeneği (.env: LOCALTONET_HTTP_VERIFY).
     */
    private function applySslVerifyOption(\Illuminate\Http\Client\PendingRequest $request): \Illuminate\Http\Client\PendingRequest
    {
        $verify = config('services.localtonet.http_verify', true);
        if (is_bool($verify)) {
            return $request->withOptions(['verify' => $verify]);
        }
        if (is_string($verify) && $verify !== '' && is_readable($verify)) {
            return $request->withOptions(['verify' => $verify]);
        }

        return $request->withOptions(['verify' => true]);
    }

    /**
     * Bearer auth + SSL doğrulama (.env: LOCALTONET_HTTP_VERIFY).
     */
    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
        ]);

        return $this->applySslVerifyOption($request);
    }

    /**
     * Bearer gerektirmeyen localtonet.com istekleri (android/changeip, restartphone).
     */
    protected function httpPublicLocaltonet(): \Illuminate\Http\Client\PendingRequest
    {
        return $this->applySslVerifyOption(Http::timeout(60));
    }

    public function triggerAndroidChangeIp(string $uToken): \Illuminate\Http\Client\Response
    {
        return $this->httpPublicLocaltonet()->get(
            'https://localtonet.com/android/changeip?u='.rawurlencode($uToken)
        );
    }

    public function triggerAndroidRestartPhone(string $uToken): \Illuminate\Http\Client\Response
    {
        return $this->httpPublicLocaltonet()->get(
            'https://localtonet.com/android/restartphone?u='.rawurlencode($uToken)
        );
    }

    public function getAvailableServers()
    {
        $response = $this->http()->get($this->apiUrl.'GetAvailableServers');

        return $response->json();
    }

    public function createProxyTunnel($protocolType, $serverCode, $authToken, ?string $localServerIp = null)
    {
        $payload = [
            'title' => 'test',
            'protocolType' => $protocolType,
            'serverCode' => $serverCode,
            'authToken' => $authToken,
        ];
        if ($localServerIp !== null && $localServerIp !== '') {
            // Güncel API: proxy tüneli için yerel sunucu IP (istemci tarafı); boş olunca "Local Server Ip can not be null!"
            $payload['localServerIp'] = $localServerIp;
        }

        $response = $this->http()->post($this->apiUrl.'CreateProxyTunnel', $payload);
        $json = $response->json();
        if ($this->createProxyTunnelNeedsPascalLocalServerIp($json) && $localServerIp !== null && $localServerIp !== '') {
            $p2 = $payload;
            unset($p2['localServerIp']);
            $p2['LocalServerIp'] = $localServerIp;
            $json = $this->http()->post($this->apiUrl.'CreateProxyTunnel', $p2)->json();
        }

        return $json;
    }

    /**
     * v2 API: tünel argümanları (örn. "--net Ethernet0 --ip 1.2.3.4"). arguments null ile temizlenir.
     */
    public function patchTunnelArguments($tunnelId, ?string $arguments): array
    {
        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/'.rawurlencode((string) $tunnelId).'/arguments';

        try {
            $response = $this->http()->asJson()->patch($url, [
                'arguments' => $arguments,
            ]);
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_PATCH_TUNNEL_ARGUMENTS_EXCEPTION', [
                'tunnel_id' => $tunnelId,
                'message' => $e->getMessage(),
            ]);

            return [
                'hasError' => true,
                'errorCode' => 'EXCEPTION',
                'errors' => [$e->getMessage()],
            ];
        }

        if (! $response->successful()) {
            $body = $response->json();

            return [
                'hasError' => true,
                'errorCode' => $response->status(),
                'errors' => [
                    is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : substr($response->body(), 0, 500),
                ],
            ];
        }

        $json = $response->json();
        if (is_array($json) && ! empty($json['hasError'])) {
            return $json;
        }

        return ['hasError' => false, 'result' => $json];
    }

    /**
     * v2 API: tünel protokolü (sayısal enum; varsayılan 6=HTTP, 7=SOCKS5 — .env ile özelleştirilebilir).
     */
    public function patchTunnelProtocolType($tunnelId, int $protocolType): array
    {
        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/'.rawurlencode((string) $tunnelId).'/protocol-type';

        try {
            $response = $this->http()->asJson()->patch($url, [
                'protocolType' => $protocolType,
            ]);
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_PATCH_TUNNEL_PROTOCOL_TYPE_EXCEPTION', [
                'tunnel_id' => $tunnelId,
                'message' => $e->getMessage(),
            ]);

            return [
                'hasError' => true,
                'errorCode' => 'EXCEPTION',
                'errors' => [$e->getMessage()],
            ];
        }

        if (! $response->successful()) {
            $body = $response->json();

            return [
                'hasError' => true,
                'errorCode' => $response->status(),
                'errors' => [
                    is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : substr($response->body(), 0, 500),
                ],
            ];
        }

        $json = $response->json();
        if (is_array($json) && ! empty($json['hasError'])) {
            return $json;
        }

        return ['hasError' => false, 'result' => $json];
    }

    /**
     * v2 API: tünel sunucu portu — PATCH .../v2/tunnels/{id}/server-port
     * Gövde: { "serverPort": int }
     *
     * @return array{hasError: bool, errorCode?: mixed, errors?: list<string>, result?: mixed}
     */
    public function patchTunnelServerPortV2(int $tunnelId, int $serverPort): array
    {
        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/'.rawurlencode((string) $tunnelId).'/server-port';

        try {
            $response = $this->http()->timeout(120)->asJson()->patch($url, [
                'serverPort' => $serverPort,
            ]);
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_PATCH_TUNNEL_SERVER_PORT_V2_EXCEPTION', [
                'tunnel_id' => $tunnelId,
                'message' => $e->getMessage(),
            ]);

            return [
                'hasError' => true,
                'errorCode' => 'EXCEPTION',
                'errors' => [$e->getMessage()],
            ];
        }

        if (! $response->successful()) {
            $body = $response->json();

            return [
                'hasError' => true,
                'errorCode' => $response->status(),
                'errors' => [
                    is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : substr($response->body(), 0, 500),
                ],
            ];
        }

        $json = $response->json();
        if (is_array($json) && ! empty($json['hasError'])) {
            return $json;
        }

        return ['hasError' => false, 'result' => $json];
    }

    /**
     * v2 toplu protokol güncelleme: PATCH .../v2/tunnels/bulk/protocol-type
     * Gövde: [ { "tunnelId": int, "protocolType": int }, ... ] (en fazla 100 öğe).
     *
     * @param  list<array{tunnelId: int, protocolType: int}>  $items
     * @return array{hasError: bool, errors?: list<string>, raw?: mixed}
     */
    public function patchTunnelsBulkProtocolType(array $items): array
    {
        if (count($items) === 0) {
            return ['hasError' => true, 'errors' => ['Boş bulk protokol listesi']];
        }
        if (count($items) > 100) {
            return ['hasError' => true, 'errors' => ['Bulk protokol isteği en fazla 100 öğe olabilir']];
        }

        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/bulk/protocol-type';

        try {
            $response = $this->http()->timeout(300)->asJson()->patch($url, $items);
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_BULK_PROTOCOL_TYPE_EXCEPTION', [
                'message' => $e->getMessage(),
            ]);

            return [
                'hasError' => true,
                'errors' => [$e->getMessage()],
            ];
        }

        $json = $response->json();

        if (! $response->successful()) {
            $msg = is_array($json)
                ? json_encode($json, JSON_UNESCAPED_UNICODE)
                : substr($response->body(), 0, 800);

            return [
                'hasError' => true,
                'errors' => ['HTTP '.$response->status().': '.$msg],
                'raw' => $json,
            ];
        }

        if (! is_array($json)) {
            return ['hasError' => false, 'result' => $json];
        }

        if (! empty($json['hasError'])) {
            $err = $json['errors'] ?? $json['message'] ?? 'Bilinmeyen hata';

            return [
                'hasError' => true,
                'errors' => is_array($err) ? array_map('strval', $err) : [(string) $err],
                'raw' => $json,
            ];
        }

        return ['hasError' => false, 'result' => $json];
    }

    /**
     * v2 toplu proxy oluşturma (tek istekte en fazla 100 kayıt).
     *
     * @param  list<array<string, mixed>>  $items  POST gövdesi doğrudan JSON dizi
     * @return array{hasError: bool, errors?: list<string>, tunnelIds?: list<int>, raw?: mixed}
     */
    public function createProxyTunnelsBulkV2WithDetail(array $items): array
    {
        if (count($items) === 0) {
            return ['hasError' => true, 'errors' => ['Boş bulk liste']];
        }
        if (count($items) > 100) {
            return ['hasError' => true, 'errors' => ['Bulk istek en fazla 100 öğe olabilir']];
        }

        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/bulk/proxy/with-detail';

        try {
            $response = $this->http()->timeout(300)->asJson()->post($url, $items);
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_BULK_PROXY_V2_EXCEPTION', [
                'message' => $e->getMessage(),
            ]);

            return [
                'hasError' => true,
                'errors' => [$e->getMessage()],
            ];
        }

        $json = $response->json();

        if (! $response->successful()) {
            $msg = is_array($json)
                ? json_encode($json, JSON_UNESCAPED_UNICODE)
                : substr($response->body(), 0, 800);

            return [
                'hasError' => true,
                'errors' => ['HTTP '.$response->status().': '.$msg],
                'raw' => $json,
            ];
        }

        if (! is_array($json)) {
            return ['hasError' => true, 'errors' => ['Geçersiz JSON yanıt'], 'raw' => $json];
        }

        if (! empty($json['hasError'])) {
            $err = $json['errors'] ?? $json['message'] ?? 'Bilinmeyen hata';

            return [
                'hasError' => true,
                'errors' => is_array($err) ? array_map('strval', $err) : [(string) $err],
                'raw' => $json,
            ];
        }

        $expected = count($items);
        $ids = self::extractTunnelIdsFromBulkV2Response($json, $expected);
        if ($ids === null || count($ids) !== $expected) {
            Logger::info('LOCALTONET_BULK_PROXY_V2_PARSE_TRY_RECOVER', [
                'expected' => $expected,
                'parsed_count' => $ids === null ? null : count($ids),
                'keys' => array_keys($json),
            ]);
            $recovered = $this->recoverBulkProxyTunnelIdsFromApi($items);
            if ($recovered !== null && count($recovered) === $expected) {
                Logger::info('LOCALTONET_BULK_PROXY_V2_RECOVERED_VIA_GET_ALL', ['count' => $expected]);

                return ['hasError' => false, 'tunnelIds' => $recovered, 'raw' => $json];
            }
            Logger::error('LOCALTONET_BULK_PROXY_V2_PARSE', [
                'expected' => $expected,
                'keys' => array_keys($json),
                'recover_failed' => true,
            ]);

            return [
                'hasError' => true,
                'errors' => ['Toplu oluşturma yanıtı beklenen yapıda değil (tünel id listesi çıkarılamadı).'],
                'raw' => $json,
            ];
        }

        return ['hasError' => false, 'tunnelIds' => $ids, 'raw' => $json];
    }

    /**
     * GET /api/v2/tunnels/{id} — tek tünel detayını V2 endpoint ile alır.
     *
     * @return array{hasError: bool, result?: array<string, mixed>, errors?: list<string>}
     */
    public function getTunnelDetailV2(int $tunnelId): array
    {
        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/'.$tunnelId;

        try {
            $response = $this->http()->timeout(30)->get($url);
        } catch (\Throwable $e) {
            return ['hasError' => true, 'errors' => [$e->getMessage()]];
        }

        $json = $response->json();

        if (! $response->successful()) {
            return [
                'hasError' => true,
                'errors' => ['HTTP '.$response->status()],
            ];
        }

        if (! is_array($json)) {
            return ['hasError' => true, 'errors' => ['Geçersiz yanıt']];
        }

        if (! empty($json['hasError'])) {
            $err = $json['errors'] ?? $json['message'] ?? 'Bilinmeyen hata';
            return [
                'hasError' => true,
                'errors' => is_array($err) ? array_map('strval', $err) : [(string) $err],
            ];
        }

        $result = $json['result'] ?? $json;
        if (isset($result['hasError'])) {
            unset($result['hasError']);
        }

        return ['hasError' => false, 'result' => $result];
    }

    /**
     * @return array{hasError: bool, errors?: list<string>, tunnels?: list<array<string, mixed>>, raw?: mixed}
     */
    public function getAllTunnelsV2(): array
    {
        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels';

        try {
            $response = $this->http()->timeout(180)->get($url);
        } catch (\Throwable $e) {
            return ['hasError' => true, 'errors' => [$e->getMessage()]];
        }

        $json = $response->json();

        if (! $response->successful()) {
            $msg = is_array($json)
                ? json_encode($json, JSON_UNESCAPED_UNICODE)
                : substr($response->body(), 0, 500);

            return [
                'hasError' => true,
                'errors' => ['HTTP '.$response->status().': '.$msg],
                'raw' => $json,
            ];
        }

        if (! is_array($json)) {
            return ['hasError' => true, 'errors' => ['Geçersiz tünel listesi'], 'raw' => $json];
        }

        if (! empty($json['hasError'])) {
            $err = $json['errors'] ?? $json['message'] ?? 'Bilinmeyen hata';

            return [
                'hasError' => true,
                'errors' => is_array($err) ? array_map('strval', $err) : [(string) $err],
                'raw' => $json,
            ];
        }

        if (array_is_list($json)) {
            return ['hasError' => false, 'tunnels' => $json];
        }
        if (isset($json['result']) && is_array($json['result'])) {
            return ['hasError' => false, 'tunnels' => array_values($json['result'])];
        }
        if (isset($json['data']) && is_array($json['data'])) {
            return ['hasError' => false, 'tunnels' => array_values($json['data'])];
        }

        return ['hasError' => true, 'errors' => ['Tünel listesi beklenen yapıda değil'], 'raw' => $json];
    }

    /**
     * GET /v2/tunnels cevabında externalId = "order-{orderId}-{sıra}" olan kayıtların tünel id'lerini sıraya göre döndürür.
     *
     * @param  list<mixed>  $tunnels
     * @return list<int>
     */
    public static function orderedTunnelIdsForOrderFromExternalIds(int $orderId, array $tunnels): array
    {
        $prefix = 'order-'.$orderId.'-';
        $map = [];
        foreach ($tunnels as $t) {
            if (! is_array($t)) {
                continue;
            }
            $ext = $t['externalId'] ?? $t['ExternalId'] ?? null;
            if (! is_string($ext) || ! str_starts_with($ext, $prefix)) {
                continue;
            }
            $suffix = substr($ext, strlen($prefix));
            if ($suffix === '' || ! ctype_digit($suffix)) {
                continue;
            }
            $idx = (int) $suffix;
            $tid = self::extractTunnelIdFromBulkV2Row($t);
            if ($tid !== null && $tid > 0) {
                $map[$idx] = $tid;
            }
        }
        ksort($map, SORT_NUMERIC);

        return array_values($map);
    }

    /**
     * Sipariş için order-{id}-0 .. order-{id}-(expected-1) tam kümesi var mı (çift indeks / eksik / fazla yok).
     *
     * @param  list<mixed>  $tunnels
     */
    public static function externalIdRecoverySetIsCompleteForOrder(int $orderId, array $tunnels, int $expected): bool
    {
        if ($expected < 1) {
            return false;
        }
        $prefix = 'order-'.$orderId.'-';
        $map = [];
        foreach ($tunnels as $t) {
            if (! is_array($t)) {
                continue;
            }
            $ext = $t['externalId'] ?? $t['ExternalId'] ?? null;
            if (! is_string($ext) || ! str_starts_with($ext, $prefix)) {
                continue;
            }
            $suffix = substr($ext, strlen($prefix));
            if ($suffix === '' || ! ctype_digit($suffix)) {
                continue;
            }
            $idx = (int) $suffix;
            if ($idx < 0 || $idx >= $expected) {
                return false;
            }
            if (isset($map[$idx])) {
                return false;
            }
            $tid = self::extractTunnelIdFromBulkV2Row($t);
            if ($tid === null || $tid <= 0) {
                return false;
            }
            $map[$idx] = $tid;
        }

        if (count($map) !== $expected) {
            return false;
        }
        for ($i = 0; $i < $expected; $i++) {
            if (! isset($map[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Toplu yanıt eksik / bozuksa: GET /v2/tunnels ile externalId, serverPort+authToken veya title eşleştirir.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<int>|null
     */
    private function recoverBulkProxyTunnelIdsFromApi(array $items): ?array
    {
        $expected = count($items);
        if ($expected === 0) {
            return null;
        }

        $lastErr = null;
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $listRes = $this->getAllTunnelsV2();
            if (! empty($listRes['hasError'])) {
                $lastErr = $listRes['errors'][0] ?? 'get tunnels failed';
                if ($attempt < 3) {
                    sleep(1);
                }

                continue;
            }
            $tunnels = $listRes['tunnels'] ?? [];
            if (! is_array($tunnels)) {
                if ($attempt < 3) {
                    sleep(1);
                }

                continue;
            }
            $ids = self::matchBulkItemsToTunnelIds($items, $tunnels);
            if ($ids !== null && count($ids) === $expected && count(array_unique($ids)) === $expected) {
                return $ids;
            }
            if ($attempt < 3) {
                sleep(1);
            }
        }
        Logger::error('LOCALTONET_BULK_RECOVER_FAILED', [
            'expected' => $expected,
            'last_error' => $lastErr,
        ]);

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  list<mixed>  $tunnels
     * @return list<int>|null
     */
    private static function matchBulkItemsToTunnelIds(array $items, array $tunnels): ?array
    {
        $expected = count($items);
        $ids = [];

        foreach ($items as $idx => $it) {
            if (! is_array($it)) {
                return null;
            }
            $wantExt = isset($it['externalId']) && is_string($it['externalId']) ? $it['externalId'] : null;
            $wantPort = isset($it['serverPort']) ? (int) $it['serverPort'] : 0;
            $wantToken = isset($it['authToken']) ? (string) $it['authToken'] : '';
            $wantTitle = isset($it['title']) ? mb_substr((string) $it['title'], 0, 30) : '';

            $foundId = null;

            if ($wantExt !== null && $wantExt !== '') {
                foreach ($tunnels as $t) {
                    if (! is_array($t)) {
                        continue;
                    }
                    $te = $t['externalId'] ?? $t['ExternalId'] ?? null;
                    if (is_string($te) && $te === $wantExt) {
                        $foundId = self::extractTunnelIdFromBulkV2Row($t);
                        break;
                    }
                }
            }

            if ($foundId === null && $wantPort > 0 && $wantToken !== '') {
                foreach ($tunnels as $t) {
                    if (! is_array($t)) {
                        continue;
                    }
                    $tp = isset($t['serverPort']) ? (int) $t['serverPort'] : (isset($t['ServerPort']) ? (int) $t['ServerPort'] : 0);
                    $ttok = (string) ($t['authToken'] ?? $t['AuthToken'] ?? '');
                    if ($tp === $wantPort && $ttok === $wantToken) {
                        $foundId = self::extractTunnelIdFromBulkV2Row($t);
                        break;
                    }
                }
            }

            if ($foundId === null && $wantTitle !== '') {
                foreach ($tunnels as $t) {
                    if (! is_array($t)) {
                        continue;
                    }
                    $tt = isset($t['title']) ? mb_substr((string) $t['title'], 0, 30) : '';
                    if ($tt !== $wantTitle) {
                        continue;
                    }
                    if ($wantToken !== '') {
                        $ttok = (string) ($t['authToken'] ?? $t['AuthToken'] ?? '');
                        if ($ttok !== $wantToken) {
                            continue;
                        }
                    }
                    $foundId = self::extractTunnelIdFromBulkV2Row($t);
                    break;
                }
            }

            if ($foundId === null) {
                Logger::error('LOCALTONET_BULK_MATCH_ROW_MISS', [
                    'idx' => $idx,
                    'externalId' => $wantExt,
                    'serverPort' => $wantPort,
                    'title' => $wantTitle,
                ]);

                return null;
            }
            $ids[] = $foundId;
        }

        if (count($ids) !== $expected || count(array_unique($ids)) !== $expected) {
            return null;
        }

        return $ids;
    }

    /**
     * Toplu with-detail yanıt satırından tünel kimliği (Swagger: öncelik tunnelId).
     */
    public static function extractTunnelIdFromBulkV2Row(mixed $row): ?int
    {
        if (is_int($row) || (is_string($row) && ctype_digit($row))) {
            $n = (int) $row;

            return $n > 0 ? $n : null;
        }
        if (! is_array($row)) {
            return null;
        }
        if (! empty($row['hasError']) || ! empty($row['HasError'])) {
            return null;
        }

        $candidates = [
            $row['tunnelId'] ?? null,
            $row['TunnelId'] ?? null,
            $row['tunnel_id'] ?? null,
            $row['id'] ?? null,
            $row['Id'] ?? null,
            data_get($row, 'result.tunnelId'),
            data_get($row, 'result.id'),
            data_get($row, 'tunnel.id'),
        ];
        foreach ($candidates as $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $n = (int) $v;
            if ($n > 0) {
                return $n;
            }
        }

        return null;
    }

    /**
     * @return list<int>|null
     */
    public static function extractTunnelIdsFromBulkV2Response(array $json, int $expectedCount): ?array
    {
        $list = null;
        if (isset($json['result']) && is_array($json['result'])) {
            $list = $json['result'];
        } elseif (isset($json['data']) && is_array($json['data'])) {
            $list = $json['data'];
        } elseif (isset($json['tunnels']) && is_array($json['tunnels'])) {
            $list = $json['tunnels'];
        } elseif ($expectedCount > 0 && array_is_list($json) && isset($json[0])) {
            $first = $json[0];
            if (is_array($first) || is_int($first) || (is_string($first) && ctype_digit($first))) {
                $list = $json;
            }
        }

        if (! is_array($list) || count($list) !== $expectedCount) {
            return null;
        }

        $ids = [];
        foreach ($list as $row) {
            $id = self::extractTunnelIdFromBulkV2Row($row);
            if ($id === null) {
                return null;
            }
            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * IPv4 Localtonet bulk satırı (v2 /with-detail şeması).
     *
     * @param  array<string, mixed>|null  $bandwidthLimitData
     */
    public static function buildV4BulkProxyItem(
        int $protocolType,
        string $serverCode,
        string $authToken,
        string $localServerIp,
        ?string $arguments,
        ?string $externalId,
        ?array $bandwidthLimitData,
        string $title,
        string $authUsername,
        string $authPassword,
        ?string $expirationDateIso,
        int $serverPort
    ): array {
        return [
            'protocolType' => $protocolType,
            'title' => mb_substr($title, 0, 30),
            'authToken' => $authToken,
            'serverPort' => $serverPort,
            'serverCode' => $serverCode,
            'subDomainName' => null,
            'domainName' => null,
            'localServerIp' => $localServerIp,
            'isStartAfterCreate' => true,
            'expirationDate' => $expirationDateIso,
            'arguments' => $arguments,
            'externalId' => $externalId,
            'justConnectWithIpv4' => true,
            'isWifiSplitOn' => true,
            'isLegacyConnect' => null,
            'userUsbDeviceId' => null,
            'accessControlData' => [
                'isAllow' => true,
                'addresses' => [],
            ],
            'ipRestrictionData' => [
                'isAllow' => true,
                'ipAddresses' => [],
            ],
            'authenticationData' => [
                'userName' => $authUsername,
                'password' => $authPassword,
            ],
            'bandwidthLimitData' => $bandwidthLimitData,
            'rateLimitData' => null,
            'upstreamSetting' => null,
        ];
    }

    private function createProxyTunnelNeedsPascalLocalServerIp(?array $json): bool
    {
        if (! is_array($json) || empty($json['hasError'])) {
            return false;
        }
        $parts = [];
        if (isset($json['errors'])) {
            $e = $json['errors'];
            if (is_array($e)) {
                $parts[] = implode(' ', array_map('strval', $e));
            } else {
                $parts[] = (string) $e;
            }
        }
        if (isset($json['message'])) {
            $parts[] = (string) $json['message'];
        }
        $blob = strtolower(implode(' ', $parts));

        return str_contains($blob, 'local server ip');
    }

    /**
     * GetAvailableServers yanıtından serverCode ile eşleşen satırın yerel IP alanını döndürür.
     */
    public function resolveLocalServerIpForServerCode(string $serverCode): ?string
    {
        $serverCode = trim($serverCode);
        if ($serverCode === '') {
            return null;
        }

        try {
            $res = $this->getAvailableServers();
            $list = $res['result'] ?? $res['data'] ?? $res['servers'] ?? null;
            if ($list === null && is_array($res) && isset($res[0])) {
                $list = $res;
            }
            if (! is_array($list)) {
                return null;
            }
            if (isset($list['servers']) && is_array($list['servers'])) {
                $list = $list['servers'];
            }
            foreach ($list as $srv) {
                if (! is_array($srv)) {
                    continue;
                }
                $code = $srv['serverCode'] ?? $srv['code'] ?? $srv['server_code'] ?? $srv['ServerCode'] ?? null;
                if ($code === null || strcasecmp((string) $code, $serverCode) !== 0) {
                    continue;
                }
                $candidates = [
                    $srv['localServerIp'] ?? null,
                    $srv['localIp'] ?? null,
                    $srv['defaultLocalIp'] ?? null,
                    $srv['localHost'] ?? null,
                    $srv['host'] ?? null,
                    $srv['ip'] ?? null,
                    $srv['serverIp'] ?? null,
                ];
                foreach ($candidates as $ip) {
                    if ($ip !== null && $ip !== '' && filter_var(trim((string) $ip), FILTER_VALIDATE_IP)) {
                        return trim((string) $ip);
                    }
                }
            }
        } catch (\Throwable $e) {
            Logger::warning('LOCALTONET_RESOLVE_LOCAL_SERVER_IP', ['message' => $e->getMessage()]);
        }

        return null;
    }

    public function createProxyTunnelWithDetails($title,$protocolType, $serverCode, $authToken,$quota,$accessControlList,$username,$password)
    {
        $response = $this->http()->post($this->apiUrl.'CreateProxyTunnelWithDetail', [
            'title' => $title,
            'protocolType' => $protocolType,
            'serverCode' => $serverCode,
            'authToken' => $authToken,
            'accessControlData' => [
                'isAllow' => false,
                'addresses' => $accessControlList
            ],
            'bandwidthLimitData' => [
                'dataSize' => $quota,
                'dataSizeType' => 3
            ],
            'authenticationData' => [
                'userName' => $username,
                'password' => $password
            ]
        ]);

        return $response->json();
    }

    public function deleteTunnel($proxyId)
    {
        $response = $this->http()->get($this->apiUrl."DeleteTunnel/{$proxyId}");

        return $response->json();
    }

    /**
     * DELETE /api/v2/tunnels/bulk — tünelleri toplu siler.
     *
     * @param  list<int>  $tunnelIds
     * @return array{hasError: bool, errors?: list<string>}
     */
    public function bulkDeleteTunnelsV2(array $tunnelIds): array
    {
        if (count($tunnelIds) === 0) {
            return ['hasError' => false];
        }

        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/bulk';

        try {
            $response = $this->http()->timeout(120)->asJson()->delete($url, array_map('intval', $tunnelIds));
        } catch (\Throwable $e) {
            return ['hasError' => true, 'errors' => [$e->getMessage()]];
        }

        $json = $response->json();
        if (! $response->successful()) {
            return [
                'hasError' => true,
                'errors' => is_array($json) ? ($json['errors'] ?? [$response->status().' '.$response->body()]) : [$response->body()],
            ];
        }

        return ['hasError' => false, 'result' => $json];
    }

    /**
     * POST /api/v2/tunnels/bulk/actions/stop — tünelleri toplu durdurur.
     *
     * @param  list<int>  $tunnelIds
     * @return array{hasError: bool, errors?: list<string>}
     */
    public function bulkStopTunnelsV2(array $tunnelIds): array
    {
        if (count($tunnelIds) === 0) {
            return ['hasError' => false];
        }

        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/bulk/actions/stop';

        try {
            $response = $this->http()->timeout(120)->asJson()->post($url, array_map('intval', $tunnelIds));
        } catch (\Throwable $e) {
            return ['hasError' => true, 'errors' => [$e->getMessage()]];
        }

        $json = $response->json();
        if (! $response->successful()) {
            return [
                'hasError' => true,
                'errors' => is_array($json) ? ($json['errors'] ?? [$response->status().' '.$response->body()]) : [$response->body()],
            ];
        }

        return ['hasError' => false, 'result' => $json];
    }

    /**
     * POST /api/v2/tunnels/bulk/actions/start — tünelleri toplu başlatır.
     *
     * @param  list<int>  $tunnelIds
     * @return array{hasError: bool, errors?: list<string>}
     */
    public function bulkStartTunnelsV2(array $tunnelIds): array
    {
        if (count($tunnelIds) === 0) {
            return ['hasError' => false];
        }

        $base = rtrim($this->apiUrl, '/');
        $url = $base.'/v2/tunnels/bulk/actions/start';

        try {
            $response = $this->http()->timeout(120)->asJson()->post($url, array_map('intval', $tunnelIds));
        } catch (\Throwable $e) {
            return ['hasError' => true, 'errors' => [$e->getMessage()]];
        }

        $json = $response->json();
        if (! $response->successful()) {
            return [
                'hasError' => true,
                'errors' => is_array($json) ? ($json['errors'] ?? [$response->status().' '.$response->body()]) : [$response->body()],
            ];
        }

        return ['hasError' => false, 'result' => $json];
    }

    public function startTunnel($proxyId)
    {
        $response = $this->http()->get($this->apiUrl."StartTunnel/{$proxyId}");

        return $response->json();
    }

    public function stopTunnel($proxyId)
    {
        $response = $this->http()->get($this->apiUrl."StopTunnel/{$proxyId}");

        return $response->json();
    }

    public function getTunnelDetail($proxyId)
    {
        $response = $this->http()->get($this->apiUrl."GetTunnelDetail/{$proxyId}");

        return $response->json();
    }

    /**
     * GetTunnelDetail result satırından port (API camelCase / PascalCase karışabiliyor).
     */
    public static function extractServerPortFromTunnelResult(?array $r): ?int
    {
        if (! is_array($r)) {
            return null;
        }
        $p = $r['serverPort'] ?? $r['ServerPort'] ?? $r['server_port'] ?? null;
        if ($p === null || $p === '') {
            return null;
        }
        $pi = (int) $p;

        return $pi > 0 ? $pi : null;
    }

    /**
     * GetTunnelDetail yanıtında atanmış geçerli bir TCP portu var mı (500–65535).
     */
    public static function tunnelDetailHasValidServerPort(?array $detailResponse): bool
    {
        if (! is_array($detailResponse) || ! empty($detailResponse['hasError'])) {
            return false;
        }
        $r = $detailResponse['result'] ?? null;
        if (! is_array($r) || (int) ($r['id'] ?? 0) <= 0) {
            return false;
        }
        $pi = self::extractServerPortFromTunnelResult($r);
        if ($pi === null) {
            return false;
        }

        return $pi >= 500 && $pi <= 65535;
    }

    /**
     * SetServerPortForTunnel yanıtı panel tarafından reddedildi mi (OrderLocaltonetController ile aynı kural).
     */
    protected function setServerPortResponseRejected(array $setResponse): bool
    {
        if (! empty($setResponse['hasError'])) {
            return true;
        }
        if (! array_key_exists('result', $setResponse)) {
            return false;
        }

        return ! empty($setResponse['result']);
    }

    /**
     * @param  array<string, mixed>  $patchResponse  patchTunnelServerPortV2 çıktısı
     */
    protected function patchTunnelServerPortV2Rejected(array $patchResponse): bool
    {
        return ! empty($patchResponse['hasError']);
    }

    /**
     * Önce v2 PATCH dene; olmazsa eski SetServerPortForTunnel.
     *
     * @return array<string, mixed>  Birleşik yanıt (hasError ile)
     */
    public function assignTunnelServerPortPreferV2(int $tunnelId, int $serverPort): array
    {
        $patch = $this->patchTunnelServerPortV2($tunnelId, $serverPort);
        if (! $this->patchTunnelServerPortV2Rejected($patch)) {
            return $patch;
        }

        $legacy = $this->setServerPort($tunnelId, $serverPort);
        if (! is_array($legacy)) {
            $legacy = ['hasError' => true, 'errors' => ['SetServerPort geçersiz yanıt']];
        }
        if ($this->setServerPortResponseRejected($legacy)) {
            $legErr = $legacy['errors'] ?? [];
            $legParts = is_array($legErr) ? $legErr : [(string) $legErr];

            return [
                'hasError' => true,
                'errors' => array_merge(
                    $patch['errors'] ?? ['v2 server-port PATCH başarısız'],
                    $legParts
                ),
                'v2' => $patch,
                'legacy' => $legacy,
            ];
        }

        return ['hasError' => false, 'result' => $legacy, 'via' => 'legacy'];
    }

    /**
     * Manuel port ataması için aralık (varsayılan 5 hane: 10000–99999).
     *
     * @return array{0: int, 1: int}
     */
    public static function manualV4ServerPortRange(): array
    {
        $v4cfg = config('services.localtonet_v4', []);
        $min = (int) ($v4cfg['manual_assign_port_min'] ?? 10000);
        $max = (int) ($v4cfg['manual_assign_port_max'] ?? 99999);
        if ($min < 500) {
            $min = 500;
        }
        if ($max > 65535) {
            $max = 65535;
        }
        if ($min >= $max) {
            return [10000, 99999];
        }

        return [$min, $max];
    }

    /**
     * @param  list<int>  $avoidPorts
     */
    public static function randomManualV4ServerPort(array $avoidPorts = []): int
    {
        [$min, $max] = self::manualV4ServerPortRange();
        $avoid = array_flip(array_map('intval', $avoidPorts));

        for ($i = 0; $i < 64; $i++) {
            $p = random_int($min, $max);
            if (! isset($avoid[$p])) {
                return $p;
            }
        }

        return random_int($min, $max);
    }

    /**
     * Oluşturma anında çakışmasın diye sipariş başına birbirinden farklı manuel port listesi (5 hane aralığı).
     *
     * @return list<int>
     */
    public static function allocateDistinctManualServerPorts(int $count): array
    {
        if ($count < 1) {
            return [];
        }
        [$min, $max] = self::manualV4ServerPortRange();
        $span = $max - $min + 1;
        if ($count > $span) {
            Logger::error('LOCALTONET_V4_MANUAL_PORT_POOL_EXHAUSTED', [
                'requested' => $count,
                'span' => $span,
            ]);

            return [];
        }

        $used = [];
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $found = false;
            for ($t = 0; $t < 512; $t++) {
                $p = random_int($min, $max);
                if (! isset($used[$p])) {
                    $used[$p] = true;
                    $out[] = $p;
                    $found = true;

                    break;
                }
            }
            if (! $found) {
                for ($p = $min; $p <= $max; $p++) {
                    if (! isset($used[$p])) {
                        $used[$p] = true;
                        $out[] = $p;
                        $found = true;

                        break;
                    }
                }
            }
            if (! $found) {
                break;
            }
        }

        return $out;
    }

    /**
     * Rezerve + durdur + (iç döngüde) farklı 5 haneli portlar dene + başlat.
     */
    public function ensureV4TunnelServerPortAssigned(int $tunnelId): bool
    {
        $detail = $this->getTunnelDetail($tunnelId);
        if (self::tunnelDetailHasValidServerPort($detail)) {
            return true;
        }

        // Toplu oluşturma / PATCH sonrası port alanı boş kalabiliyor; StartTunnel ile API tarafı güncellenir.
        $kick = $this->startTunnel($tunnelId);
        if (! empty($kick['hasError'])) {
            Logger::warning('LOCALTONET_V4_PORT_ENSURE_KICK_START', [
                'tunnel_id' => $tunnelId,
                'errors' => $kick['errors'] ?? [],
            ]);
        }
        $detail = $this->getTunnelDetail($tunnelId);
        if (self::tunnelDetailHasValidServerPort($detail)) {
            return true;
        }

        $v4cfg = config('services.localtonet_v4', []);
        $maxAttempts = (int) ($v4cfg['port_assign_max_attempts'] ?? 20);
        $innerTries = (int) ($v4cfg['port_assign_inner_tries'] ?? 8);
        $quickPatchTries = (int) ($v4cfg['port_v2_quick_patch_tries'] ?? 12);
        if ($maxAttempts < 1) {
            $maxAttempts = 1;
        }
        if ($innerTries < 1) {
            $innerTries = 1;
        }
        if ($quickPatchTries < 1) {
            $quickPatchTries = 1;
        }

        // Önce durdurmadan v2 PATCH (çoğu kurulumda daha hızlı ve güvenilir)
        $triedQuick = [];
        for ($q = 0; $q < $quickPatchTries; $q++) {
            $qp = self::randomManualV4ServerPort($triedQuick);
            $triedQuick[] = $qp;
            $patch = $this->patchTunnelServerPortV2($tunnelId, $qp);
            if ($this->patchTunnelServerPortV2Rejected($patch)) {
                Logger::warning('LOCALTONET_V2_PATCH_PORT_QUICK_FAIL', [
                    'tunnel_id' => $tunnelId,
                    'port' => $qp,
                    'errors' => $patch['errors'] ?? [],
                ]);

                continue;
            }
            $this->startTunnel($tunnelId);
            $detail = $this->getTunnelDetail($tunnelId);
            if (self::tunnelDetailHasValidServerPort($detail)) {
                return true;
            }
        }

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $detail = $this->getTunnelDetail($tunnelId);
            if (self::tunnelDetailHasValidServerPort($detail)) {
                return true;
            }
            $proxy = is_array($detail) ? ($detail['result'] ?? []) : [];
            if (! is_array($proxy) || (int) ($proxy['id'] ?? 0) <= 0) {
                break;
            }

            if (isset($proxy['status']) && (int) $proxy['status'] === 0) {
                $start = $this->startTunnel($tunnelId);
                if (! empty($start['hasError'])) {
                    Logger::warning('LOCALTONET_V4_PORT_ENSURE_START', [
                        'tunnel_id' => $tunnelId,
                        'errors' => $start['errors'] ?? [],
                    ]);
                }
                $detail = $this->getTunnelDetail($tunnelId);
                $proxy = is_array($detail) ? ($detail['result'] ?? []) : [];
            }

            if (empty($proxy['isReserved'])) {
                $res = $this->changePortReservedStatus($tunnelId, true);
                if (! empty($res['hasError'])) {
                    Logger::warning('LOCALTONET_V4_PORT_ENSURE_RESERVE', [
                        'tunnel_id' => $tunnelId,
                        'errors' => $res['errors'] ?? [],
                    ]);
                }
            }

            $stop = $this->stopTunnel($tunnelId);
            if (! empty($stop['hasError'])) {
                Logger::warning('LOCALTONET_V4_PORT_ENSURE_STOP', [
                    'tunnel_id' => $tunnelId,
                    'errors' => $stop['errors'] ?? [],
                ]);
            }

            $triedThisRound = [];
            for ($inner = 0; $inner < $innerTries; $inner++) {
                $targetPort = self::randomManualV4ServerPort($triedThisRound);
                $triedThisRound[] = $targetPort;

                $assigned = $this->assignTunnelServerPortPreferV2($tunnelId, $targetPort);
                if (! empty($assigned['hasError'])) {
                    Logger::warning('LOCALTONET_V4_PORT_ASSIGN_V2_OR_LEGACY_FAILED', [
                        'tunnel_id' => $tunnelId,
                        'port' => $targetPort,
                        'inner' => $inner,
                        'errors' => $assigned['errors'] ?? [],
                    ]);

                    continue;
                }

                $start2 = $this->startTunnel($tunnelId);
                if (! empty($start2['hasError'])) {
                    Logger::warning('LOCALTONET_V4_PORT_ENSURE_START_AFTER_SET', [
                        'tunnel_id' => $tunnelId,
                        'errors' => $start2['errors'] ?? [],
                    ]);
                }

                $detail = $this->getTunnelDetail($tunnelId);
                if (self::tunnelDetailHasValidServerPort($detail)) {
                    return true;
                }
                $stopRetry = $this->stopTunnel($tunnelId);
                if (! empty($stopRetry['hasError'])) {
                    Logger::warning('LOCALTONET_V4_PORT_ENSURE_STOP_AFTER_AMBIGUOUS_SET', [
                        'tunnel_id' => $tunnelId,
                        'errors' => $stopRetry['errors'] ?? [],
                    ]);
                }
            }

            $this->startTunnel($tunnelId);
        }

        return self::tunnelDetailHasValidServerPort($this->getTunnelDetail($tunnelId));
    }

    public function getAuthTokens()
    {
        return Cache::remember($this->authTokensCacheKey(), 60, function () {
            try {
                $response = $this->http()->get($this->apiUrl.'GetAuthTokens');
                if (! $response->successful()) {
                    return ['result' => []];
                }

                $json = $response->json();

                return is_array($json) ? $json : ['result' => []];
            } catch (\Throwable $e) {
                \Log::warning('Localtonet GetAuthTokens: '.$e->getMessage());

                return ['result' => []];
            }
        });
    }

    public function updateTitle($tunnelId, $title)
    {
        $response = $this->http()->post($this->apiUrl.'UpdateTitle', [
            'tunnelId' => $tunnelId,
            'title' => mb_substr($title, 0, 30) // max 30 character
        ]);

        return $response->json();
    }

    public function getAuthenticationDataByTunnelId($proxyId)
    {
        $response = $this->http()->get($this->apiUrl."GetAuthenticationDataByTunnelId/{$proxyId}");

        return $response->json();
    }

    public function setAuthenticationForTunnel($tunnelId, $isActive, $userName = null, $password = null)
    {
        $response = $this->http()->post($this->apiUrl.'SetAuthenticationForTunnel', [
            'tunnelId' => $tunnelId,
            'isActive' => (bool)$isActive,
            'userName' => $userName ?? Str::random(6),
            'password' => $password ?? Str::random(6),
        ]);

        return $response->json();
    }

    public function setServerPort($tunnelId, $serverPort)
    {
        $response = $this->http()->post($this->apiUrl.'SetServerPortForTunnel', [
            'tunnelId' => $tunnelId,
            'serverPort' => $serverPort
        ]);

        return $response->json();
    }

    public function getIpHistoryByAuthToken($authToken)
    {
        if ($authToken === null || $authToken === '') {
            return ['hasError' => true, 'errorCode' => 'NO_AUTH_TOKEN', 'errors' => ['Auth token eksik'], 'result' => null];
        }

        try {
            $response = $this->http()->timeout(300)->get(
                $this->apiUrl.'GetIpHistoryByAuthToken/'.rawurlencode($authToken)
            );

            if (! $response->successful()) {
                return [
                    'hasError' => true,
                    'errorCode' => $response->status(),
                    'errors' => [substr($response->body(), 0, 500)],
                    'result' => null,
                ];
            }

            $json = $response->json();

            return is_array($json) ? $json : ['hasError' => true, 'errors' => ['Geçersiz yanıt'], 'result' => null];
        } catch (\Throwable $e) {
            \Log::warning('Localtonet GetIpHistoryByAuthToken: '.$e->getMessage());

            return [
                'hasError' => true,
                'errorCode' => 'EXCEPTION',
                'errors' => [$e->getMessage()],
                'result' => null,
            ];
        }
    }

    public function addIpRestriction($tunnelId, $ipAddress)
    {
        $response = $this->http()->post($this->apiUrl.'AddIpRestriction', [
            'tunnelId' => $tunnelId,
            'ipAddress' => $ipAddress
        ]);

        return $response->json();
    }

    public function updateIsAllowForIpRestriction($tunnelId, $isAllow)
    {
        $response = $this->http()->post($this->apiUrl.'UpdateIsAllowForIpRestriction', [
            'tunnelId' => $tunnelId,
            'isAllow' => (bool)$isAllow
        ]);

        return $response->json();
    }

    public function deleteAllIpRestrictions($tunnelId)
    {
        $response = $this->http()->post($this->apiUrl.'DeleteAllIpRestrictions', [
            'tunnelId' => $tunnelId,
        ]);

        return $response->json();
    }

    public function changePortReservedStatus($proxyId, $status)
    {
        $response = $this->http()->get($this->apiUrl."ChangePortReservedStatus/{$proxyId}/{$status}");

        return $response->json();
    }

    public function getAirplaneModeSettings($authToken)
    {
        $response = $this->http()->get($this->apiUrl."GetAirplaneModeSettingsByAuthToken/{$authToken}");

        $data = $response->json();
        //airplaneModeLinkType: 0 change ip, 1 restart device
        $airplaneModeLinks = collect(@$data["result"]["airplaneModeLinks"])->groupBy("airplaneModeLinkType")->toArray();
        $data["result"]["deviceRestartLink"] = @$airplaneModeLinks[1][0];
        $data["result"]["ipChangeLink"] = @$airplaneModeLinks[0][0];

        return $data;
    }

    public function setAutoAirplaneModeSetting($authToken, $isAirPlaneModeOn, $time = 30)
    {
        $response = $this->http()->post($this->apiUrl.'SetAutoAirplaneModeSetting', [
            'authToken' => $authToken,
            'isAirPlaneModeOn' => (bool)$isAirPlaneModeOn,
            'time' => $time, //en az 30
            'delay' => 2, //sabit 2
        ]);

        return $response->json();
    }

    public function setBandwidthLimitForTunnel($tunnelId, $dataSize, $dataSizeType)
    {
        $response = $this->http()->post($this->apiUrl.'SetBandwidthLimitForTunnel', [
            'tunnelId' => $tunnelId,
            'dataSize' => $dataSize,
            'dataSizeType' => $dataSizeType,
        ]);

        return $response->json();
    }

    public function addAccessControl($address, $tunnelId)
    {
        $response = $this->http()->post($this->apiUrl.'AddAccessControl', [
            'address' => $address,
            'tunnelId' => $tunnelId,
        ]);

        return $response->json();
    }

    public function getExpirationDateByTunnelId($tunnelId)
    {
        $response = $this->http()->get($this->apiUrl."GetExpirationDateByTunnelId/{$tunnelId}");

        return $response->json();
    }

    public function setExpirationDateForTunnel($tunnelId, $expirationDate)
    {
        $response = $this->http()->post($this->apiUrl.'SetExpirationDateForTunnel', [
            'tunnelId' => $tunnelId,
            'expirationDate' => $expirationDate,
        ]);

        return $response->json();
    }

    /* ================================================================
     *  Shared Proxy Client API  (legacy endpoints)
     * ================================================================ */

    /**
     * POST /api/AddClientForSharedProxyTunnel
     */
    public function createSharedProxyClient(int $tunnelId, array $data): array
    {
        $data['tunnelId'] = $tunnelId;

        try {
            $response = $this->http()->post($this->apiUrl.'AddClientForSharedProxyTunnel', $data);
        } catch (\Throwable $e) {
            return [
                'hasError' => true,
                'errorCode' => 'EXCEPTION',
                'errors' => [$e->getMessage()],
            ];
        }

        $json = $response->json();
        if (is_array($json) && ! empty($json['hasError'])) {
            return $json;
        }

        return ['hasError' => false, 'result' => $json['result'] ?? $json];
    }

    /**
     * POST /api/UpdateClientForSharedProxyTunnel
     */
    public function updateSharedProxyClient(int $tunnelId, string $clientId, array $data): array
    {
        $data['id'] = $clientId;
        $data['tunnelId'] = $tunnelId;

        try {
            $response = $this->http()->post($this->apiUrl.'UpdateClientForSharedProxyTunnel', $data);
        } catch (\Throwable $e) {
            return [
                'hasError' => true,
                'errorCode' => 'EXCEPTION',
                'errors' => [$e->getMessage()],
            ];
        }

        $json = $response->json();
        if (is_array($json) && ! empty($json['hasError'])) {
            return $json;
        }

        return ['hasError' => false, 'result' => $json['result'] ?? $json];
    }

    /**
     * POST /api/DeleteClientForSharedProxyTunnel
     */
    public function deleteSharedProxyClient(int $tunnelId, string $clientId): array
    {
        try {
            $response = $this->http()->post($this->apiUrl.'DeleteClientForSharedProxyTunnel', [
                'id' => $clientId,
                'tunnelId' => $tunnelId,
            ]);
        } catch (\Throwable $e) {
            return [
                'hasError' => true,
                'errorCode' => 'EXCEPTION',
                'errors' => [$e->getMessage()],
            ];
        }

        $json = $response->json();
        if (is_array($json) && ! empty($json['hasError'])) {
            return $json;
        }

        return ['hasError' => false, 'result' => $json['result'] ?? $json];
    }

    /**
     * GET /api/GetSharedProxyClientsByTunnelId/{tunnelId}
     */
    public function listSharedProxyClients(int $tunnelId): array
    {
        try {
            $response = $this->http()->get($this->apiUrl.'GetSharedProxyClientsByTunnelId/'.$tunnelId);
        } catch (\Throwable $e) {
            return [
                'hasError' => true,
                'errorCode' => 'EXCEPTION',
                'errors' => [$e->getMessage()],
            ];
        }

        $json = $response->json();
        if (is_array($json) && ! empty($json['hasError'])) {
            return $json;
        }

        return ['hasError' => false, 'result' => $json['result'] ?? $json];
    }

}
