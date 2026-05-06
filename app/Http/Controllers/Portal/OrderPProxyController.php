<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PProxySettings;
use App\Services\PlainProxiesApiService;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OrderPProxyController extends Controller
{
    use AjaxResponses;

    private function resolveOrder(Order $order): ?Order
    {
        if ($order->user_id !== Auth::id()) {
            return null;
        }
        if (!$order->isPProxyDelivery()) {
            return null;
        }
        return $order;
    }

    public function getSubUserInfo(Order $order)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $pi   = $order->product_info ?? [];
        $uuid = $pi['pproxy_uuid'] ?? null;
        if (!$uuid) {
            return response()->json(['success' => false, 'message' => 'Sub-user bulunamadı']);
        }

        $cacheKey = 'PPROXY_SUBUSER_' . $uuid;
        $data = Cache::remember($cacheKey, 30, function () use ($uuid) {
            $service = new PlainProxiesApiService();
            $res = $service->getSubUserInfo($uuid);
            if ($res && ($res['success'] ?? false)) {
                return $res['data'] ?? null;
            }
            return null;
        });

        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Veri alınamadı']);
    }

    public function generateProxies(Order $order, Request $request)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $pi   = $order->product_info ?? [];
        $uuid = $pi['pproxy_uuid'] ?? null;
        if (!$uuid) {
            return response()->json(['success' => false, 'message' => 'Sub-user bulunamadı']);
        }

        $amount   = min(max((int) $request->input('proxy_amount', 10), 1), 1000);
        $country  = $request->input('country', '');
        $state    = $request->input('state', '');
        $city     = $request->input('city', '');
        $rotation = strtolower($request->input('rotation', 'rotating'));
        $format   = $request->input('format', '{ip}:{port}:{user}:{pass}');
        $lifetime = (int) $request->input('lifetime', 1);
        $protocol = strtolower($request->input('protocol', 'http'));

        $payload = [
            'subUserId'        => $uuid,
            'usernamePassword' => true,
            'proxyCount'       => $amount,
            'rotation'         => $rotation,
            'format'           => $format,
            'lifetime'         => $lifetime,
        ];

        if ($country && $country !== 'WW') {
            $payload['country'] = $country;
        }
        if ($state) {
            $payload['state'] = $state;
        }
        if ($city) {
            $payload['city'] = $city;
        }

        $service = new PlainProxiesApiService();
        $res = $service->generateProxyList($payload);

        if ($res && ($res['success'] ?? false)) {
            $data = $res['data'] ?? [];
            $proxies = $data['proxies'] ?? $data;

            $serverDomain = $this->resolveServerDomain($order);

            if (is_array($proxies)) {
                $proxies = array_map(function ($p) use ($serverDomain, $state, $protocol) {
                    $p = str_replace('res-v2.pr.plainproxies.com', $serverDomain, $p);
                    if ($protocol === 'socks5') {
                        $p = preg_replace('/:8080:/', ':1080:', $p, 1);
                    }
                    if ($state) {
                        $p = $this->injectStateSuffix($p, $state);
                    }
                    return $p;
                }, $proxies);

                if ($rotation === 'rotating' && count($proxies) === 1 && $amount > 1) {
                    $proxies = array_fill(0, $amount, $proxies[0]);
                }
            }

            return response()->json(['success' => true, 'data' => $proxies]);
        }

        $errMsg = $res['data']['message'] ?? ($res['data']['error'] ?? 'Proxy oluşturulamadı');
        return response()->json(['success' => false, 'message' => $errMsg]);
    }

    private function injectStateSuffix(string $proxyLine, string $stateCode): string
    {
        $parts = explode(':', $proxyLine);
        if (count($parts) < 3) {
            return $proxyLine;
        }

        $username = $parts[2];

        if (str_contains($username, '-state-')) {
            return $proxyLine;
        }

        $stateSuffix = '-state-' . strtolower($stateCode);

        $cityPos = strpos($username, '-city-');
        $sessionPos = strpos($username, '-session-');

        if ($cityPos !== false) {
            $username = substr($username, 0, $cityPos) . $stateSuffix . substr($username, $cityPos);
        } elseif ($sessionPos !== false) {
            $username = substr($username, 0, $sessionPos) . $stateSuffix . substr($username, $sessionPos);
        } else {
            $username .= $stateSuffix;
        }

        $parts[2] = $username;
        return implode(':', $parts);
    }

    public function getCountries()
    {
        $cacheKey = 'PPROXY_RESI_COUNTRIES_SLIM';
        $data = Cache::remember($cacheKey, 3600, function () {
            $service = new PlainProxiesApiService();
            $res = $service->getResidentialCountries();
            if (!$res || !($res['success'] ?? false)) {
                return [];
            }

            $countries = $res['data'] ?? [];
            $slim = [];
            foreach ($countries as $c) {
                $states = [];
                if (!empty($c['states']) && is_array($c['states'])) {
                    foreach ($c['states'] as $sk => $sv) {
                        $states[$sk] = [
                            'name'   => $sv['name'] ?? $sk,
                            'cities' => $sv['cities'] ?? [],
                        ];
                    }
                }

                $cities = [];
                if (!empty($c['cities']) && is_array($c['cities'])) {
                    foreach ($c['cities'] as $city) {
                        $cities[] = [
                            'code'       => $city['code'] ?? $city['ascii'] ?? '',
                            'name'       => $city['name'] ?? '',
                            'state'      => (string) ($city['state'] ?? ''),
                            'state_name' => $city['state_name'] ?? '',
                        ];
                    }
                }

                $slim[] = [
                    'name'   => $c['name'] ?? '',
                    'iso2'   => $c['iso2'] ?? '',
                    'states' => $states,
                    'cities' => $cities,
                ];
            }
            return $slim;
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function resolveServerDomain(Order $order): string
    {
        $product = $order->product;
        $productDomain = $product?->delivery_items['pproxy_server_domain'] ?? null;
        if ($productDomain && trim($productDomain) !== '') {
            return trim($productDomain);
        }

        $settings = PProxySettings::first();
        if ($settings && $settings->server_domain && trim($settings->server_domain) !== '') {
            return trim($settings->server_domain);
        }

        return 'tr.saglamproxy.com';
    }

    public function getServerDomain(Order $order)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $domain = $this->resolveServerDomain($order);
        return response()->json(['success' => true, 'domain' => $domain]);
    }

    public function getTrafficInfo(Order $order)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $data = $order->getPProxySubUserDataFresh();
        if ($data) {
            $apiBandwidthGb = round($data['bandwidth'] / 1000000000, 2);
            $pi = $order->product_info ?? [];
            $storedQuota = (float) ($pi['pproxy_quota_gb'] ?? 0);
            if ($apiBandwidthGb > 0 && abs($apiBandwidthGb - $storedQuota) > 0.01) {
                $pi['pproxy_quota_gb'] = $apiBandwidthGb;
                $order->product_info = $pi;
                $order->saveQuietly();
            }

            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Veri alınamadı']);
    }

    public function changePassword(Order $order, Request $request)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $pi   = $order->product_info ?? [];
        $uuid = $pi['pproxy_uuid'] ?? null;
        if (!$uuid) {
            return response()->json(['success' => false, 'message' => 'Sub-user bulunamadı']);
        }

        $newPassword = $request->input('new_password');
        if (!$newPassword || strlen($newPassword) < 4 || strlen($newPassword) > 12) {
            return response()->json(['success' => false, 'message' => 'Şifre 4-12 karakter arasında olmalıdır.']);
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $newPassword)) {
            return response()->json(['success' => false, 'message' => 'Şifre sadece harf ve rakam içerebilir.']);
        }

        $service = new PlainProxiesApiService();
        $res = $service->updateSubUserV2($uuid, ['newPassword' => $newPassword]);

        if ($res && ($res['success'] ?? false)) {
            $pi['pproxy_password'] = $newPassword;
            $order->product_info = $pi;
            $order->saveQuietly();

            Cache::forget('PPROXY_SUBUSER_' . $uuid);

            return response()->json(['success' => true, 'message' => 'Şifre başarıyla değiştirildi.', 'password' => $newPassword]);
        }

        $errMsg = $res['data']['message'] ?? 'Şifre değiştirilemedi.';
        return response()->json(['success' => false, 'message' => $errMsg]);
    }

    public function testProxy(Order $order, Request $request)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $protocol  = strtolower($request->input('protocol', 'http'));
        $proxyLine = $request->input('proxy_line', '');

        $scheme = $protocol === 'socks5' ? 'socks5' : 'http';

        if ($proxyLine) {
            $parts = explode(':', $proxyLine, 4);
            if (count($parts) === 4) {
                $host = $parts[0];
                $port = $parts[1];
                $user = $parts[2];
                $pass = $parts[3];
                $proxyUrl = "{$scheme}://{$user}:{$pass}@{$host}:{$port}";
            } else {
                return response()->json(['success' => false, 'message' => 'Geçersiz proxy formatı']);
            }
        } else {
            $pi       = $order->product_info ?? [];
            $username = $pi['pproxy_username'] ?? '';
            $password = $pi['pproxy_password'] ?? '';
            if (!$username || !$password) {
                return response()->json(['success' => false, 'message' => 'Proxy bilgileri eksik']);
            }
            $serverDomain = $this->resolveServerDomain($order);
            $port         = $protocol === 'socks5' ? 1080 : 8080;
            $proxyUrl     = "{$scheme}://{$username}:{$password}@{$serverDomain}:{$port}";
        }

        $testUrl = 'http://ip-api.com/json/?fields=status,country,regionName,city,isp,query';

        try {
            $start = microtime(true);
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withOptions(['proxy' => $proxyUrl])
                ->get($testUrl);
            $elapsed = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    return response()->json([
                        'success'  => true,
                        'online'   => true,
                        'latency'  => $elapsed,
                        'protocol' => strtoupper($protocol),
                        'ip'       => $data['query'] ?? '-',
                        'location' => trim(($data['country'] ?? '') . ', ' . ($data['regionName'] ?? '') . ', ' . ($data['city'] ?? ''), ', '),
                        'isp'      => $data['isp'] ?? '-',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'online'  => false,
                'latency' => $elapsed,
                'message' => 'Proxy yanıt verdi ama geçersiz sonuç döndü.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'online'  => false,
                'latency' => 0,
                'message' => 'Bağlantı kurulamadı: ' . $e->getMessage(),
            ]);
        }
    }
}
