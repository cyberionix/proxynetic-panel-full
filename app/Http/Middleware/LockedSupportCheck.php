<?php

namespace App\Http\Middleware;

use App\Traits\AjaxResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LockedSupportCheck
{
    use AjaxResponses;

    public function handle(Request $request, Closure $next): Response
    {
        if (isset($request->support) && $request->support->is_locked == 1){
            return $this->errorResponse(__("locked_support_info_message"));
        }
        return $next($request);
    }
}
