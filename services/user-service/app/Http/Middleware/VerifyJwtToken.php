<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the RS256 JWT on every protected request using the Auth service's
 * public key.  No call is made to the Auth service — verification is fully local.
 *
 * Distributed revocation: after signature verification the token's JTI is
 * checked against the shared Redis revocation list (key pattern: revoked:{jti}).
 * Revocations written by the Auth service are therefore immediately honoured
 * by every microservice that shares the same Redis instance.
 */
class VerifyJwtToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthorized('Missing bearer token');
        }

        try {
            $publicKey = $this->resolvePublicKey();

            if (! $publicKey) {
                return $this->unauthorized('JWT public key not configured');
            }

            $decoded = JWT::decode($token, new Key($publicKey, config('jwt.algorithm', 'RS256')));
            $claims  = (array) $decoded;

            // ── Distributed revocation check ──────────────────────────────────
            // The Auth service writes 'revoked:{jti} = 1' to Redis on logout /
            // forced revocation.  Every microservice checks the same store so
            // revocations take effect immediately across the entire platform.
            $jti = $claims['jti'] ?? '';
            if ($jti && $this->isRevoked($jti)) {
                return $this->unauthorized('Token has been revoked');
            }

            $request->attributes->set('jwt_claims', $claims);
            $request->attributes->set('user_id', $claims['sub'] ?? null);
            $request->attributes->set('tenant_id', $claims['tenant_id'] ?? null);
            $request->attributes->set('roles', (array) ($claims['roles'] ?? []));
            $request->attributes->set('permissions', (array) ($claims['permissions'] ?? []));
        } catch (\Throwable $e) {
            Log::warning('JWT verification failed', ['error' => $e->getMessage()]);

            return $this->unauthorized('Invalid or expired token');
        }

        return $next($request);
    }

    /**
     * Check the shared Redis revocation list maintained by the Auth service.
     */
    private function isRevoked(string $jti): bool
    {
        try {
            return (bool) Redis::exists("revoked:{$jti}");
        } catch (\Throwable $e) {
            // If Redis is unavailable, log and fail open to avoid a hard
            // dependency on Redis for read-path performance.
            Log::warning('Redis revocation check failed — failing open', ['jti' => $jti, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function resolvePublicKey(): ?string
    {
        // Priority 1: inline key from env / config
        $inline = config('jwt.public_key');
        if ($inline) {
            return $inline;
        }

        // Priority 2: key file path
        $path = config('jwt.public_key_path');
        if ($path && file_exists($path)) {
            return file_get_contents($path) ?: null;
        }

        return null;
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'success' => false,
            'data'    => null,
            'meta'    => [],
            'errors'  => ['token' => $message],
            'message' => 'Unauthenticated',
        ], 401);
    }
}
