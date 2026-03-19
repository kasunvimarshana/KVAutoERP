<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant-aware rate limiting.
 * Limits requests per tenant per endpoint to prevent abuse.
 */
class TenantRateLimit
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $tenantId = $request->attributes->get('tenant_id')
            ?? $request->input('tenant_id')
            ?? $request->ip();

        $key = 'rl:' . sha1($tenantId . '|' . $request->route()?->getName() . '|' . $request->method());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'data'    => null,
                'meta'    => [],
                'errors'  => ['rate_limit' => 'Too many requests'],
                'message' => "Rate limit exceeded. Retry after {$retryAfter} seconds.",
            ], 429)->withHeaders([
                'Retry-After'               => $retryAfter,
                'X-RateLimit-Limit'         => $maxAttempts,
                'X-RateLimit-Remaining'     => 0,
                'X-RateLimit-Reset'         => now()->addSeconds($retryAfter)->timestamp,
            ]);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        $remaining = max(0, $maxAttempts - RateLimiter::attempts($key));

        return $response->withHeaders([
            'X-RateLimit-Limit'     => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }
}
