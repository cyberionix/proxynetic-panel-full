<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Sabit token (örnek: "mysecrettoken")
        try {
            $validToken = 'qwQRqJlKMMsF8fl5PzOwofo1izMuk0egbGB4TCAEVvisJbI0sP7RIWi9z2ruBkMb';

            $header = $request->header('Authorization');

            if (!$header || !str_starts_with($header, 'Basic ')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $encoded = substr($header, 6); // 'Basic ' kısmını at
            $decoded = base64_decode($encoded);
            [$username, $password] = explode(':', $decoded, 2);

            // Kullanıcı adı sabit, şifre token olacak şekilde
            if ($username !== 'apiuser' || $password !== $validToken)
                return response()->json(['error' => 'Unauthorized'], 401);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);

        }


        return $next($request);
    }
}
