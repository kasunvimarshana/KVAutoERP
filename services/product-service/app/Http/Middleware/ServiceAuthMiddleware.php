<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates bearer tokens by calling the Auth Service.
 * Stateless authentication that respects microservice boundaries.
 *
 * Environment variables:
 *   TOKEN_CACHE_TTL     - seconds to cache a validated token (default: 30)
 *   AUTH_BYPASS_ENABLED - set to "true" to skip auth validation (local dev only)
 */
class ServiceAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('*/health') || $request->is('up')) {
            return $next($request);
        }

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'No authentication token provided.'], 401);
        }

        $ttl      = (int) env('TOKEN_CACHE_TTL', 30);
        $cacheKey = 'token_user_' . hash('sha256', $token);

        $userData = Cache::remember($cacheKey, $ttl, function () use ($token) {
            try {
                $authUrl  = config('services.auth.url', 'http://auth_service');
                $response = Http::timeout(5)
                    ->withHeaders(['Authorization' => "Bearer {$token}"])
                    ->post("{$authUrl}/api/tokens/validate");

                if ($response->successful() && $response->json('valid')) {
                    return $response->json('user');
                }
                return null;
            } catch (\Throwable $e) {
                Log::warning('Auth service call failed', ['error' => $e->getMessage()]);
                return null;
            }
        });

        if (!$userData) {
            if (env('AUTH_BYPASS_ENABLED', 'false') === 'true') {
                Log::warning('Auth bypass enabled — skipping token validation');
                return $next($request);
            }
            return response()->json(['success' => false, 'message' => 'Invalid or expired token.'], 401);
        }

        $request->attributes->set('auth_user', $userData);
        $request->attributes->set('auth_user_id', $userData['id'] ?? null);
        $request->attributes->set('auth_roles', $userData['roles'] ?? []);

        if (!$request->attributes->get('tenant_id')) {
            $request->attributes->set('tenant_id', $userData['tenant_id'] ?? null);
        }

        return $next($request);
    }
}
