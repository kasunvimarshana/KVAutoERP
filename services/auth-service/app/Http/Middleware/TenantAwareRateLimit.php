<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant-aware rate limiter for authentication endpoints.
 * Keys are namespaced by tenant + IP + action to prevent cross-tenant interference.
 */
class TenantAwareRateLimit
{
    public function __construct(
        private readonly RateLimiter $limiter,
    ) {}

    public function handle(Request $request, Closure $next, string $action = 'login'): Response
    {
        $config = config("rate_limit.{$action}", ['max_attempts' => 5, 'decay_minutes' => 1]);
        $key = $this->resolveKey($request, $action);

        if ($this->limiter->tooManyAttempts($key, $config['max_attempts'])) {
            $seconds = $this->limiter->availableIn($key);

            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again in ' . $seconds . ' seconds.',
                'error'   => 'RATE_LIMITED',
                'retry_after' => $seconds,
            ], 429)->withHeaders([
                'X-RateLimit-Limit'     => $config['max_attempts'],
                'X-RateLimit-Remaining' => 0,
                'Retry-After'           => $seconds,
            ]);
        }

        $this->limiter->hit($key, $config['decay_minutes'] * 60);

        $response = $next($request);

        $remaining = max(0, $config['max_attempts'] - $this->limiter->attempts($key));

        return $response->withHeaders([
            'X-RateLimit-Limit'     => $config['max_attempts'],
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }

    private function resolveKey(Request $request, string $action): string
    {
        $tenantId = $request->input('tenant_id', 'global');
        $ip = $request->ip() ?? 'unknown';

        return "rate_limit:{$action}:{$tenantId}:{$ip}";
    }
}
