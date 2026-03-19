<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies an inbound JWT Bearer token from any other microservice.
 * Decodes the payload from the Base64 middle segment without verifying the
 * signature (signature verification against the Auth service public key is
 * the full implementation — here we extract claims for tenant/user context).
 *
 * In production, replace the base64-decode approach with full RS256 signature
 * verification using the Auth service public key stored at JWT_PUBLIC_KEY_PATH.
 */
class VerifyServiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return $this->unauthorized('No bearer token provided.');
        }

        $payload = $this->decodePayload($token);

        if ($payload === null) {
            return $this->unauthorized('Invalid token format.');
        }

        if ($this->isExpired($payload)) {
            return $this->unauthorized('Token has expired.');
        }

        if (empty($payload['tenant_id'])) {
            return $this->unauthorized('Token is missing required tenant_id claim.');
        }

        // Attach decoded claims to request attributes for controllers
        $request->attributes->set('jwt_payload', $payload);
        $request->attributes->set('tenant_id', $payload['tenant_id']);
        $request->attributes->set('user_id', $payload['user_id'] ?? $payload['sub'] ?? null);

        return $next($request);
    }

    /**
     * Decode the JWT payload segment (Base64URL → JSON).
     */
    private function decodePayload(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        $decoded = base64_decode(strtr($parts[1], '-_', '+/'), strict: false);

        if ($decoded === false) {
            return null;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) ? $payload : null;
    }

    private function isExpired(array $payload): bool
    {
        return isset($payload['exp']) && $payload['exp'] < time();
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error'   => 'UNAUTHORIZED',
        ], 401);
    }
}
