<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-IP rate limiting for auth endpoints.
 *
 * Usage in route definition:
 *   ->middleware('auth.ratelimit:login')
 *   ->middleware('auth.ratelimit:refresh')
 *
 * Limits are driven by the auth_service config:
 *   auth_service.rate_limits.{$type}.max_attempts
 *   auth_service.rate_limits.{$type}.decay_minutes
 */
final class RateLimitAuthMiddleware
{
    public function __construct(
        private readonly RateLimiter $rateLimiter,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request                      $request
     * @param  Closure(Request): Response   $next
     * @param  string                       $type     Rate-limit bucket name (e.g. 'login').
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $type = 'login'): Response
    {
        $config      = config("auth_service.rate_limits.{$type}", []);
        $maxAttempts = (int) ($config['max_attempts'] ?? 10);
        $decayMins   = (int) ($config['decay_minutes'] ?? 1);

        $key = $this->resolveKey($request, $type);

        if ($this->rateLimiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->rateLimiter->availableIn($key);

            return ApiResponse::error(
                message: "Too many {$type} attempts. Please try again in {$seconds} seconds.",
                errors: ['retry_after' => $seconds],
                statusCode: 429,
            );
        }

        $this->rateLimiter->hit($key, $decayMins * 60);

        /** @var Response $response */
        $response = $next($request);

        // Clear the rate-limit counter on successful auth responses (2xx).
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->rateLimiter->clear($key);
        }

        return $response;
    }

    /**
     * Build a rate-limit key from the request IP and endpoint type.
     *
     * @param  Request  $request
     * @param  string   $type
     * @return string
     */
    private function resolveKey(Request $request, string $type): string
    {
        $ip = $request->ip() ?? '0.0.0.0';

        return "auth_ratelimit:{$type}:{$ip}";
    }
}
