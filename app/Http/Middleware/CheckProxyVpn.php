<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProxyVpn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (App::environment('local')){
            return $next($request);
        }
        if ($request->getMethod() != 'POST'){
            return $next($request);
        }
        $ipAddress = $request->ip();

        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return abort(403,'Geçersiz IP adresi tespit edildi.');
        }

//        $data = file_get_contents('https://api.ip2location.io/?key=08EAB2E6EF4B0ED4643F7B595031ED40&ip=' . $ipAddress);
        $data = file_get_contents('https://api.findip.net/'.$ipAddress.'/?token=b4c93c670d0442a6a698fe91a28ba9df');

        $data = json_decode($data);

        $block = $data->traits->user_type == 'hosting';

        if ($block){
            if (Auth::check()){
                Auth::logout();
            }
            return response([
                'success' => false,
                'message' => 'Proxy/VPN kullanımı tespit edildi. İşleme devam edemezsiniz.'
            ]);

        }

        return $next($request);
    }
}
