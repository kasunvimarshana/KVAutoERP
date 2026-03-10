<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ServiceAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token required.',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        $cacheKey = 'token_validation:' . hash('sha256', $token);
        $userData = Cache::remember($cacheKey, 60, function () use ($token) {
            try {
                $response = Http::withToken($token)
                    ->timeout(5)
                    ->post(config('services.auth.introspect_url'), ['token' => $token]);

                if ($response->successful() && $response->json('data.active')) {
                    return $response->json('data');
                }
            } catch (\Exception $e) {
                return null;
            }
            return null;
        });

        if (!$userData) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.',
                'error_code' => 'INVALID_TOKEN',
            ], 401);
        }

        $request->attributes->set('user', $userData);
        $request->attributes->set('user_id', $userData['user_id']);

        return $next($request);
    }
}
