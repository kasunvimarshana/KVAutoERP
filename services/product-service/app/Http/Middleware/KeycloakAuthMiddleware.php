<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class KeycloakAuthMiddleware
{
    private string $keycloakBaseUrl;
    private string $realm;

    public function __construct()
    {
        $this->keycloakBaseUrl = config('keycloak.base_url');
        $this->realm           = config('keycloak.realm');
    }

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'No authentication token provided.',
            ], 401);
        }

        try {
            $tokenData = $this->introspectToken($token);

            if (!$tokenData || !($tokenData['active'] ?? false)) {
                return response()->json([
                    'error'   => 'Unauthorized',
                    'message' => 'Invalid or expired token.',
                ], 401);
            }

            // Check required roles (RBAC)
            if (!empty($roles) && !$this->hasRequiredRoles($tokenData, $roles)) {
                return response()->json([
                    'error'   => 'Forbidden',
                    'message' => 'Insufficient permissions.',
                ], 403);
            }

            // Attach user data to request for downstream use
            $request->merge(['auth_user' => $tokenData]);
            $request->setUserResolver(fn () => (object) $tokenData);

        } catch (\Exception $e) {
            Log::error('Keycloak authentication error', ['error' => $e->getMessage()]);
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Authentication service error.',
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

    private function introspectToken(string $token): ?array
    {
        $cacheKey = 'keycloak_token_' . md5($token);

        return Cache::remember($cacheKey, 60, function () use ($token) {
            $response = Http::asForm()->post(
                "{$this->keycloakBaseUrl}/realms/{$this->realm}/protocol/openid-connect/token/introspect",
                [
                    'token'         => $token,
                    'client_id'     => config('keycloak.client_id'),
                    'client_secret' => config('keycloak.client_secret'),
                ]
            );

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        });
    }

    private function hasRequiredRoles(array $tokenData, array $requiredRoles): bool
    {
        // Extract realm roles from token
        $realmRoles = $tokenData['realm_access']['roles'] ?? [];

        // Extract client roles
        $clientId    = config('keycloak.client_id');
        $clientRoles = $tokenData['resource_access'][$clientId]['roles'] ?? [];

        $allRoles = array_merge($realmRoles, $clientRoles);

        foreach ($requiredRoles as $role) {
            if (!in_array($role, $allRoles)) {
                return false;
            }
        }

        return true;
    }
}
