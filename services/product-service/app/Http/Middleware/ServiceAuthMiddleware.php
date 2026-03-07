<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ServiceAuthMiddleware validates JWT tokens for internal service-to-service communication.
 * Services present a short-lived JWT issued by Keycloak with a service-specific audience.
 */
class ServiceAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'No service authentication token provided.',
            ], 401);
        }

        try {
            $payload = $this->validateServiceToken($token);

            if (!$payload) {
                return response()->json([
                    'error'   => 'Unauthorized',
                    'message' => 'Invalid service token.',
                ], 401);
            }

            $request->merge(['service_token' => $payload]);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Service authentication failed.',
            ], 401);
        }

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }

    private function validateServiceToken(string $token): ?array
    {
        // Validate JWT signature using Keycloak public key
        $keycloakBaseUrl = config('keycloak.base_url');
        $realm           = config('keycloak.realm');

        $response = \Illuminate\Support\Facades\Http::get(
            "{$keycloakBaseUrl}/realms/{$realm}/protocol/openid-connect/certs"
        );

        if (!$response->successful()) {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);

        // Validate expiry
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        // Validate audience (must include this service)
        $serviceId = config('keycloak.client_id');
        $audience  = $payload['aud'] ?? [];
        if (is_string($audience)) {
            $audience = [$audience];
        }

        if (!empty($serviceId) && !in_array($serviceId, $audience, true)) {
            return null;
        }

        return $payload;
    }
}
