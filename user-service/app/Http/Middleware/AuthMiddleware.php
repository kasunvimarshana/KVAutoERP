<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        try {
            $secret  = env('JWT_SECRET', 'secret');
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            $user = User::find($decoded->sub ?? $decoded->user_id ?? null);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $request->user = $user;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token', 'detail' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
