<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Application\Contracts\Services\TenantConfigServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant Middleware
 * 
 * Resolves and validates the current tenant from the request.
 * Binds the tenant to the DI container for use throughout the request lifecycle.
 * 
 * Tenant is resolved from (in priority order):
 * 1. X-Tenant-ID header
 * 2. tenant_id in request body
 * 3. tenant subdomain (e.g., tenant1.api.example.com)
 * 4. Default from environment
 */
class TenantMiddleware
{
    public function __construct(
        private readonly TenantConfigServiceInterface $tenantConfigService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant identification required. Provide X-Tenant-ID header.',
                'error_code' => 'TENANT_REQUIRED',
            ], 400);
        }

        $tenant = $this->tenantConfigService->getActiveTenant($tenantId);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => "Tenant '{$tenantId}' not found or inactive.",
                'error_code' => 'TENANT_NOT_FOUND',
            ], 404);
        }

        // Bind tenant to the DI container for use in HasTenantScope trait and services
        App::instance('current.tenant', $tenant);
        App::instance('current.tenant.id', $tenant->id);

        // Set tenant on request for downstream use
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenant->id);

        return $next($request);
    }

    /**
     * Resolve tenant ID from multiple sources.
     */
    private function resolveTenantId(Request $request): ?string
    {
        // 1. X-Tenant-ID header (preferred for API calls)
        if ($tenantId = $request->header('X-Tenant-ID')) {
            return $tenantId;
        }

        // 2. Request body/query parameter
        if ($tenantId = $request->input('tenant_id')) {
            return $tenantId;
        }

        // 3. Subdomain (for multi-tenant SaaS web apps)
        $host = $request->getHost();
        if (str_contains($host, '.')) {
            $subdomain = explode('.', $host)[0];
            if (!in_array($subdomain, ['www', 'api', 'app'])) {
                return $subdomain;
            }
        }

        // 4. Default tenant from environment
        return config('tenant.id');
    }
}
