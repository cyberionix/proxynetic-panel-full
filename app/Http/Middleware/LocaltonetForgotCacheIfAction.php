<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LocaltonetForgotCacheIfAction
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        if (isset($request->order)) {
            foreach ($request->order->getAllLocaltonetProxyIds() as $tid) {
                Cache::forget('LOCALTONET_PR_DATA_'.$tid);
            }
        }
    }
}
