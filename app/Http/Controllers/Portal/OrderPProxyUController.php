<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PProxyUPool;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class OrderPProxyUController extends Controller
{
    use AjaxResponses;

    private function resolveOrder(Order $order): ?Order
    {
        if ($order->user_id !== Auth::id()) {
            return null;
        }
        if (!$order->isPProxyUDelivery()) {
            return null;
        }
        return $order;
    }

    public function generateProxies(Order $order, Request $request)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $pi = $order->product_info ?? [];
        $poolIp   = $pi['pproxyu_pool_ip'] ?? '';
        $poolPort = $pi['pproxyu_pool_port'] ?? '';
        $poolUser = $pi['pproxyu_pool_user'] ?? '';
        $poolPass = $pi['pproxyu_pool_pass'] ?? '';

        if (!$poolIp || !$poolUser) {
            return response()->json(['success' => false, 'message' => 'Proxy bilgileri eksik']);
        }

        $amount   = min(max((int) $request->input('proxy_amount', 10), 1), 1000);
        $country  = $request->input('country', '');
        $state    = $request->input('state', '');
        $city     = $request->input('city', '');
        $rotation = strtolower($request->input('rotation', 'rotating'));
        $format   = $request->input('format', '{ip}:{port}:{user}:{pass}');
        $lifetime = (int) $request->input('lifetime', 1);
        $protocol = strtolower($request->input('protocol', 'http'));

        $port = $protocol === 'socks5' ? 1080 : (int) $poolPort;

        $proxies = [];
        for ($i = 0; $i < $amount; $i++) {
            $user = $poolUser;

            if ($country && $country !== 'WW') {
                $user .= '-country-' . strtoupper($country);
            }
            if ($state) {
                $user .= '-state-' . strtolower($state);
            }
            if ($city) {
                $user .= '-city-' . strtolower($city);
            }

            if ($rotation === 'sticky') {
                $sessionId = 'sess' . mt_rand(100000, 999999);
                $user .= '-session-' . $sessionId . '-lifetime-' . $lifetime;
            }

            $line = str_replace(
                ['{ip}', '{port}', '{user}', '{pass}'],
                [$poolIp, $port, $user, $poolPass],
                $format
            );

            $proxies[] = $line;
        }

        return response()->json(['success' => true, 'data' => $proxies]);
    }

    public function getCountries(Order $order)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $pi = $order->product_info ?? [];
        $poolIp   = $pi['pproxyu_pool_ip'] ?? '';
        $poolPort = $pi['pproxyu_pool_port'] ?? '';
        $poolUser = $pi['pproxyu_pool_user'] ?? '';
        $poolPass = $pi['pproxyu_pool_pass'] ?? '';

        if (!$poolIp || !$poolUser) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $cacheKey = 'PPROXYU_COUNTRIES_' . md5($poolIp . $poolPort . $poolUser);
        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($poolIp, $poolPort, $poolUser, $poolPass) {
            try {
                $proxyUrl = "http://{$poolUser}:{$poolPass}@{$poolIp}:{$poolPort}";

                $testUrls = [
                    'https://lumtest.com/myip.json',
                    'http://ip-api.com/json/?fields=country,countryCode',
                ];

                return [];
            } catch (\Throwable $e) {
                return [];
            }
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getServerDomain(Order $order)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $pi = $order->product_info ?? [];
        $domain = $pi['pproxyu_pool_ip'] ?? 'proxy.example.com';

        return response()->json(['success' => true, 'domain' => $domain]);
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
            $pi = $order->product_info ?? [];
            $poolIp   = $pi['pproxyu_pool_ip'] ?? '';
            $poolPort = $pi['pproxyu_pool_port'] ?? '';
            $poolUser = $pi['pproxyu_pool_user'] ?? '';
            $poolPass = $pi['pproxyu_pool_pass'] ?? '';
            if (!$poolIp || !$poolUser) {
                return response()->json(['success' => false, 'message' => 'Proxy bilgileri eksik']);
            }
            $port = $protocol === 'socks5' ? 1080 : $poolPort;
            $proxyUrl = "{$scheme}://{$poolUser}:{$poolPass}@{$poolIp}:{$port}";
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

    public function changePassword(Order $order, Request $request)
    {
        $order = $this->resolveOrder($order);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Sipariş bulunamadı']);
        }

        $newPassword = $request->input('new_password');
        if (!$newPassword || strlen($newPassword) < 4 || strlen($newPassword) > 12) {
            return response()->json(['success' => false, 'message' => 'Şifre 4-12 karakter arasında olmalıdır.']);
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $newPassword)) {
            return response()->json(['success' => false, 'message' => 'Şifre sadece harf ve rakam içerebilir.']);
        }

        $pi = $order->product_info ?? [];
        $pi['pproxyu_password'] = $newPassword;
        $order->product_info = $pi;
        $order->saveQuietly();

        return response()->json(['success' => true, 'message' => 'Şifre başarıyla değiştirildi.', 'password' => $newPassword]);
    }
}
