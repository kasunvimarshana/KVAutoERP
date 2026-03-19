<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simplified service token middleware.
 *
 * Decodes the JWT payload (base64url) and injects tenant/user context
 * into request attributes so controllers can perform tenant-scoped queries.
 *
 * Signature verification is delegated to the API gateway layer.
 * This middleware only ensures a token is present and parseable.
 */
class VerifyServiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return $this->unauthorized('No token provided.');
        }

        $payload = $this->decodePayload($token);

        if ($payload === null) {
            return $this->unauthorized('Invalid token format.');
        }

        if (empty($payload['tenant_id'])) {
            return $this->unauthorized('Token is missing required tenant_id claim.');
        }

        if (empty($payload['sub']) && empty($payload['user_id'])) {
            return $this->unauthorized('Token is missing required user identity claim.');
        }

        $request->attributes->set('jwt_payload', $payload);
        $request->attributes->set('tenant_id', $payload['tenant_id']);
        $request->attributes->set('user_id', $payload['user_id'] ?? $payload['sub'] ?? '');

        return $next($request);
    }

    /**
     * Decode the JWT payload section (second segment) from base64url.
     * Returns null if the token structure is invalid or JSON is malformed.
     */
    private function decodePayload(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        $payloadBase64 = $this->base64UrlDecode($parts[1]);

        if ($payloadBase64 === false) {
            return null;
        }

        $payload = json_decode($payloadBase64, true);

        return is_array($payload) ? $payload : null;
    }

    private function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;
        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'), true);
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
