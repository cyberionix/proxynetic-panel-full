<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckProxyVpn
{
    public function handle(Request $request, Closure $next): Response
    {
        if (App::environment('local')) {
            return $next($request);
        }

        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        $ipAddress = $request->ip();

        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return abort(403, 'Geçersiz IP adresi tespit edildi.');
        }

        $isPost = $request->getMethod() === 'POST';
        $user = Auth::guard('web')->user();
        $userVpnBlocked = $user && $user->security && $user->security->is_cant_vpn == 1;

        if (!$isPost && !$userVpnBlocked) {
            return $next($request);
        }

        $ipData = $this->getIpData($ipAddress);

        if (!$ipData) {
            return $next($request);
        }

        $isHosting = ($ipData->traits->user_type ?? '') === 'hosting';

        if ($isPost && $isHosting) {
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }
            return response([
                'success' => false,
                'message' => 'Proxy/VPN kullanımı tespit edildi. İşleme devam edemezsiniz.'
            ]);
        }

        if ($userVpnBlocked && $isHosting) {
            Auth::guard('web')->logout();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proxy/VPN kullanımı tespit edildi. Oturumunuz sonlandırıldı.'
                ], 403);
            }

            return redirect()->route('portal.auth.login')
                ->withErrors(['vpn' => 'Proxy/VPN kullanımı tespit edildi. Bu hesap ile VPN/Proxy kullanarak giriş yapamazsınız.']);
        }

        return $next($request);
    }

    private function getIpData(string $ip): ?object
    {
        $cacheKey = 'vpn_check_ip_' . md5($ip);

        return Cache::remember($cacheKey, 600, function () use ($ip) {
            try {
                $response = file_get_contents('https://api.findip.net/' . $ip . '/?token=b4c93c670d0442a6a698fe91a28ba9df');
                return json_decode($response);
            } catch (\Throwable $e) {
                Log::warning('VPN_CHECK_API_FAIL', ['ip' => $ip, 'error' => $e->getMessage()]);
                return null;
            }
        });
    }
}
