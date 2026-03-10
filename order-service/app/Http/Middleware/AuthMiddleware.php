<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        // Also check x-user-id header (set by API Gateway)
        $userId = $request->header('x-user-id');

        if ($userId) {
            $request->user_id = (int) $userId;
            return $next($request);
        }

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        try {
            $secret  = env('JWT_SECRET', 'secret');
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $request->user_id = (int) ($decoded->sub ?? $decoded->user_id ?? 0);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
