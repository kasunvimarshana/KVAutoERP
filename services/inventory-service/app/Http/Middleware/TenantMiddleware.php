<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->resolveTenantId($request);

        if (empty($tenantId)) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Tenant identifier is required. Provide the ' . config('tenant.header', 'X-Tenant-ID') . ' header.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Validate tenant against tenant-service (with cache to avoid repeated calls)
        $tenant = $this->fetchTenant($tenantId);

        if ($tenant === null) {
            return response()->json([
                'error'   => 'Forbidden',
                'message' => "Tenant [{$tenantId}] not found or is inactive.",
            ], Response::HTTP_FORBIDDEN);
        }

        // Attach tenant context to the request for downstream use
        $request->attributes->set('tenant_id', $tenantId);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }

    private function resolveTenantId(Request $request): ?string
    {
        $header = config('tenant.header', 'X-Tenant-ID');

        // 1. Try the configured header
        $tenantId = $request->header($header);

        // 2. Try JWT claim 'tenant_id' as a fallback
        if (empty($tenantId) && $request->bearerToken()) {
            $tenantId = $this->extractTenantFromJwt($request->bearerToken());
        }

        return ! empty($tenantId) ? (string) $tenantId : null;
    }

    private function extractTenantFromJwt(string $token): ?string
    {
        try {
            $parts   = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return $payload['tenant_id'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function fetchTenant(string $tenantId): ?array
    {
        $cacheKey = "tenant:{$tenantId}";
        $ttl      = (int) config('tenant.cache_ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () use ($tenantId): ?array {
            $serviceUrl = config('tenant.service_url', 'http://tenant-service:8002');

            try {
                $response = Http::timeout(5)
                    ->acceptJson()
                    ->get("{$serviceUrl}/v1/tenants/{$tenantId}");

                if ($response->successful()) {
                    $data   = $response->json();
                    $tenant = $data['data'] ?? $data;

                    // Only allow active tenants
                    if (($tenant['status'] ?? 'active') !== 'active') {
                        return null;
                    }

                    return $tenant;
                }

                return null;
            } catch (\Throwable $e) {
                Log::warning("[TenantMiddleware] Could not reach tenant-service: {$e->getMessage()}");
                // Fail open during tenant-service outage — log the failure but allow the request
                return ['id' => $tenantId, 'status' => 'active'];
            }
        });
    }
}
