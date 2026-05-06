<?php

namespace App\Services;

use App\Library\Logger;
use Illuminate\Support\Facades\Http;

class PlainProxiesApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null)
    {
        $settings = \App\Models\PProxySettings::first();
        $this->baseUrl = rtrim($baseUrl ?? $settings?->api_base_url ?? 'https://dashboard.plainproxies.com', '/');
        $this->apiKey  = $apiKey ?? $settings?->api_key ?? '';
    }

    private function request(string $method, string $path, array $data = []): ?array
    {
        if (empty($this->apiKey)) {
            Logger::error('PPROXY_NO_API_KEY', ['message' => 'API Key ayarlanmamış']);
            return null;
        }

        try {
            $http = Http::timeout(30)
                ->withoutVerifying()
                ->withHeaders([
                    'X-API-KEY'    => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ]);

            $url = $this->baseUrl . $path;

            $res = match (strtoupper($method)) {
                'GET'    => $http->get($url, $data),
                'POST'   => $http->post($url, $data),
                'PUT'    => $http->put($url, $data),
                'DELETE' => $http->delete($url, $data),
                default  => $http->get($url),
            };

            Logger::info('PPROXY_API_RESPONSE', [
                'method' => $method,
                'path'   => $path,
                'status' => $res->status(),
                'body'   => mb_substr($res->body(), 0, 500),
            ]);

            return [
                'success' => $res->successful(),
                'status'  => $res->status(),
                'data'    => $res->json(),
            ];
        } catch (\Throwable $e) {
            Logger::error('PPROXY_REQUEST_FAIL', [
                'method' => $method,
                'path'   => $path,
                'error'  => $e->getMessage(),
            ]);
            return ['success' => false, 'status' => 0, 'data' => null];
        }
    }

    // ── Sub Users ──

    public function createResidentialSubUser(int $days, float $bandwidthGb, ?string $name = null): ?array
    {
        $payload = [
            'days'      => $days,
            'bandwidth' => $bandwidthGb,
        ];
        if ($name) {
            $payload['name'] = $name;
        }
        return $this->request('POST', '/api/v1/res/sub-users/residential/add', $payload);
    }

    public function getSubUserInfo(string $uuid): ?array
    {
        return $this->request('GET', '/api/v1/res/sub-users/info/' . $uuid);
    }

    public function getAllSubUsers(string $ownerUuid): ?array
    {
        return $this->request('GET', '/api/v1/res/sub-users/info/' . $ownerUuid);
    }

    public function updateSubUser(string $uuid, array $data): ?array
    {
        return $this->request('PUT', '/api/v1/res/sub-users/update/' . $uuid, $data);
    }

    public function updateSubUserV2(string $uuid, array $data): ?array
    {
        return $this->request('PUT', '/api/v1/res/sub-users/update-v2/' . $uuid, $data);
    }

    public function setBandwidth(string $uuid, float $newBandwidthGb): ?array
    {
        return $this->request('PUT', '/api/v1/res/sub-users/update-v2/' . $uuid, [
            'newBandwidth' => $newBandwidthGb,
        ]);
    }

    public function destroySubUser(string $uuid): ?array
    {
        return $this->request('DELETE', '/api/v1/res/sub-users/destroy/' . $uuid);
    }

    public function refreshSubUserIPs(string $uuid): ?array
    {
        return $this->request('POST', '/api/v1/res/sub-users/refresh-ips/' . $uuid);
    }

    // ── Proxy Generation ──

    public function generateProxyList(array $data): ?array
    {
        return $this->request('GET', '/api/v1/res/products/access/generate', $data);
    }

    // ── Countries ──

    public function getResidentialCountries(): ?array
    {
        return $this->request('GET', '/api/v1/res/products/countries/residential');
    }

    // ── IP List ──

    public function getIpList(string $subUserId): ?array
    {
        return $this->request('POST', '/api/v1/res/products/ip-list', [
            'subUserId' => $subUserId,
        ]);
    }

    // ── Owner / Reseller ──

    public function getOwnerProfile(): ?array
    {
        return $this->request('GET', '/api/v1/res/owner/profile');
    }

    // ── Convenience: provision a new order ──

    public function provisionOrder(int $orderId, float $bandwidthGb, int $days): ?array
    {
        $name = 'pnet' . str_pad($orderId, 8, '0', STR_PAD_LEFT);

        $res = $this->createResidentialSubUser($days, $bandwidthGb, $name);

        if (!$res || !($res['success'] ?? false)) {
            Logger::error('PPROXY_PROVISION_FAIL', [
                'order_id' => $orderId,
                'status'   => $res['status'] ?? 'null',
                'response' => $res['data'] ?? null,
            ]);
            return null;
        }

        $responseData = $res['data'] ?? [];
        $subUserData  = $responseData['data'] ?? $responseData;

        $uuid      = $subUserData['uuid'] ?? null;
        $proxyInfo = $subUserData['proxy_information'] ?? [];
        $username  = $proxyInfo['ipv4_resi_proxy_username'] ?? $subUserData['username'] ?? null;
        $password  = $proxyInfo['ipv4_resi_proxy_password'] ?? $subUserData['password'] ?? null;
        $activeUntil = $subUserData['active_until'] ?? null;

        if (!$uuid) {
            Logger::error('PPROXY_UUID_MISSING', [
                'order_id' => $orderId,
                'response' => $responseData,
            ]);
            return null;
        }

        return [
            'uuid'         => $uuid,
            'username'     => $username,
            'password'     => $password,
            'server_ip'    => $proxyInfo['server_ip'] ?? null,
            'server_port'  => $proxyInfo['server_port'] ?? null,
            'quota_gb'     => $bandwidthGb,
            'days'         => $days,
            'active_until' => $activeUntil,
            'raw'          => $subUserData,
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'API Key girilmemiş.'];
        }

        $res = $this->request('GET', '/api/v1/res/owner/profile');
        if ($res && ($res['success'] ?? false)) {
            return ['success' => true, 'message' => 'Bağlantı başarılı.'];
        }

        $status = $res['status'] ?? 0;
        if ($status === 401 || $status === 403) {
            return ['success' => false, 'message' => 'API Key geçersiz (HTTP ' . $status . ')'];
        }

        return ['success' => false, 'message' => 'API sunucusuna bağlanılamadı (HTTP ' . $status . ')'];
    }
}
