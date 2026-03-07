<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RateLimitMiddleware
{
    /**
     * Rate-limit requests using a per-tenant (or per-IP) sliding counter in Redis.
     *
     * @param int $maxAttempts Maximum requests allowed within the decay window.
     * @param int $decayMinutes Length of the rate-limit window in minutes.
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 100, int $decayMinutes = 1): mixed
    {
        // Use tenant ID if present, fall back to client IP
        $identifier = $request->header('X-Tenant-ID') ?? $request->ip();
        $key        = 'rate_limit:' . $identifier;

        $attempts = (int) (Redis::get($key) ?? 0);

        if ($attempts >= $maxAttempts) {
            $retryAfter = (int) Redis::ttl($key);

            Log::warning('Rate limit exceeded', [
                'identifier' => $identifier,
                'attempts'   => $attempts,
                'limit'      => $maxAttempts,
            ]);

            return response()->json([
                'error'       => 'Too Many Requests',
                'retry_after' => $retryAfter,
            ], 429)->withHeaders([
                'X-RateLimit-Limit'     => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'Retry-After'           => $retryAfter,
                'X-RateLimit-Reset'     => now()->addSeconds($retryAfter)->timestamp,
            ]);
        }

        if ($attempts === 0) {
            // First request in this window: set key with expiry
            Redis::setex($key, $decayMinutes * 60, 1);
        } else {
            Redis::incr($key);
        }

        $remaining = max(0, $maxAttempts - $attempts - 1);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit'     => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }
}
