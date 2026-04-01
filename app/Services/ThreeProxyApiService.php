<?php

namespace App\Services;

use App\Library\Logger;
use App\Models\ThreeProxyPool;
use App\Models\ThreeProxyPoolServer;

class ThreeProxyApiService
{
    private string $baseUrl;
    private string $authUsername;
    private string $authPassword;
    private int $timeout;

    public function __construct(string $serverIp, int $port, string $authUsername, string $authPassword)
    {
        $this->baseUrl = 'http://' . $serverIp . ':' . $port;
        $this->authUsername = $authUsername;
        $this->authPassword = $authPassword;
        $this->timeout = 30;
    }

    public static function fromPool(ThreeProxyPool $pool): self
    {
        return new self($pool->server_ip, (int) $pool->port, $pool->auth_username, $pool->auth_password);
    }

    public static function fromServer(ThreeProxyPoolServer $server): self
    {
        return new self($server->server_ip, (int) $server->port, $server->auth_username, $server->auth_password);
    }

    /**
     * POST /create - Proxy oluştur
     *
     * @param string $proxydata  IP:USER:PASS formatında (çoklu satır destekler)
     * @param string $expireDatetime  YYYY-MM-DDTHH:mm formatında bitiş tarihi
     * @return array API yanıtı { success, Proxy_data: [...] }
     */
    public function createProxy(string $proxydata, string $expireDatetime): array
    {
        return $this->post('/create', [
            'proxydata' => $proxydata,
            'expire_datetime' => $expireDatetime,
        ], true);
    }

    /**
     * POST /delete-proxy - Tek proxy sil
     */
    public function deleteProxy(string $proxyId): array
    {
        return $this->postJson('/delete-proxy', [
            'proxy_id' => $proxyId,
        ]);
    }

    /**
     * POST /delete-proxies-bulk - Toplu proxy silme
     */
    public function deleteProxiesBulk(array $proxyIds): array
    {
        $proxies = array_map(fn($id) => ['proxy_id' => $id], $proxyIds);
        return $this->postJson('/delete-proxies-bulk', [
            'proxies' => $proxies,
        ]);
    }

    /**
     * POST /stop-proxy
     */
    public function stopProxy(string $proxyId): array
    {
        return $this->postJson('/stop-proxy', [
            'proxy_id' => $proxyId,
        ]);
    }

    /**
     * POST /start-proxy
     */
    public function startProxy(string $proxyId): array
    {
        return $this->postJson('/start-proxy', [
            'proxy_id' => $proxyId,
        ]);
    }

    /**
     * POST /extend-proxy - Süre uzatma
     */
    public function extendProxy(string $proxyId, int $extendDays): array
    {
        return $this->postJson('/extend-proxy', [
            'proxy_id' => $proxyId,
            'extend_days' => $extendDays,
        ]);
    }

    /**
     * POST /update-proxy - Proxy güncelle (kullanıcı, şifre, expire)
     */
    public function updateProxy(string $proxyId, array $data): array
    {
        $payload = array_merge(['proxy_id' => $proxyId], $data);
        return $this->postJson('/update-proxy', $payload);
    }

    /**
     * POST /update-expire - Expire tarihi güncelle
     */
    public function updateExpire(string $proxyId, string $newExpire): array
    {
        return $this->postJson('/update-expire', [
            'proxy_id' => $proxyId,
            'new_expire' => $newExpire,
        ]);
    }

    /**
     * GET /api/proxy-list - Tüm proxy listesi
     */
    public function getProxyList(): array
    {
        return $this->get('/api/proxy-list');
    }

    /**
     * GET /api/proxies - Proxy durumları
     */
    public function getProxies(): array
    {
        return $this->get('/api/proxies');
    }

    private function post(string $endpoint, array $data, bool $formEncoded = false): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->authUsername . ':' . $this->authPassword);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        if ($formEncoded) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            Logger::error('THREEPROXY_API_CURL_ERROR', [
                'url' => $url,
                'error' => $error,
            ]);
            return ['success' => false, 'error' => 'curl_error', 'message' => $error, 'http_code' => $httpCode];
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            Logger::error('THREEPROXY_API_INVALID_RESPONSE', [
                'url' => $url,
                'http_code' => $httpCode,
                'response_preview' => substr($response, 0, 500),
            ]);
            return ['success' => false, 'error' => 'invalid_response', 'http_code' => $httpCode];
        }

        return $decoded;
    }

    private function postJson(string $endpoint, array $data): array
    {
        return $this->post($endpoint, $data, false);
    }

    private function get(string $endpoint): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->authUsername . ':' . $this->authPassword);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'error' => 'curl_error', 'message' => $error, 'http_code' => $httpCode];
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'error' => 'invalid_response', 'http_code' => $httpCode];
        }

        return $decoded;
    }
}
