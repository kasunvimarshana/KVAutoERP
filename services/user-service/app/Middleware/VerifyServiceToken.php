<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyServiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Service-Token');

        if (empty($token) || $token !== config('services.service_token')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: invalid service token.',
            ], 401);
        }

        return $next($request);
    }
}
