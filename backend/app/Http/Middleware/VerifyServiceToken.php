<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyServiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $serviceToken = $request->header('X-Service-Token');

        if (!$serviceToken) {
            return response()->json(['error' => 'Service token required'], 401);
        }

        $expectedToken = config('app.service_token_secret');

        if (!hash_equals($expectedToken, $serviceToken)) {
            return response()->json(['error' => 'Invalid service token'], 401);
        }

        return $next($request);
    }
}
