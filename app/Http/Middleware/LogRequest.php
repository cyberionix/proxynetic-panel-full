<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $ignoreRoute = [
            "portal.invoices.ajax",
            "portal.users.addresses.find",
            "portal.district.search",
            "portal.users.notifications.list",
            "portal.orders.localtonet.getProxyListTable",
            "portal.dashboard.getData",
//            "portal.dashboard",
        ];

        if (Auth::guard('web')->check() && Str::before($request->route()->getName(), '.') == "portal" && !in_array($request->route()->getName(), $ignoreRoute)) {
            ActivityLog::create([
                "route" => str_replace(".", "_", $request->route()->getName()),
                "data" => [
                    "method" => $request->getMethod(),
                    "ip" => $request->ip(),
                    "url" => $request->url(),
                    "request" => $request->all(),
                    "response" => !$request->isMethod("get") ? json_decode($response->getContent()) : ($response->getContent() ? ["success" => true] : null),
                    "device" => $request->header('User-Agent')
                ],
                "user_id" => auth()->check() ? auth()->user()->id : null
            ]);
        }

        if (!$request->isMethod("get")) {
//            Log::channel('log_request')->info("data", [
//                "method" => $request->getMethod(),
//                "user_id" => auth()->check() ? auth()->user()->id : null,
//                "ip" => $request->ip(),
//                "url" => $request->url(),
//                "request" => $request->all(),
//                "response" => $response->getContent(),
//                "device" => $request->header('User-Agent'),
//            ]);
        }
        return $response;
    }
}
