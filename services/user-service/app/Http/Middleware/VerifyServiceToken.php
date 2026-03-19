<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates that an incoming request carries a valid service-to-service token.
 * Used to protect internal API routes that should only be called by the Auth service
 * (or other trusted microservices).
 *
 * Token lookup priority:
 *  1. Authorization: Bearer {token}
 *  2. X-Service-Token: {token}
 */
class VerifyServiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->header('X-Service-Token');

        if (! $token) {
            return $this->forbidden('Missing service token');
        }

        $expected = config('services.auth_service_token', '');

        if (! $expected) {
            return $this->forbidden('Service token not configured');
        }

        if (! hash_equals($expected, $token)) {
            return $this->forbidden('Invalid service token');
        }

        return $next($request);
    }

    private function forbidden(string $message): Response
    {
        return response()->json([
            'success' => false,
            'data'    => null,
            'meta'    => [],
            'errors'  => ['service_token' => $message],
            'message' => 'Forbidden',
        ], 403);
    }
}
