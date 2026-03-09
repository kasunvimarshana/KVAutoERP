<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Tenant\Services\TenantService;
use App\Helpers\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant Resolution Middleware.
 *
 * Identifies the current tenant from the request and applies its
 * runtime configuration. Supports multiple resolution strategies:
 *   1. Subdomain  : tenant.example.com
 *   2. Header     : X-Tenant-ID
 *   3. JWT Claim  : token payload 'tenant_id'
 *   4. Query param: ?tenant=slug (development only)
 */
class TenantMiddleware
{
    public function __construct(
        private readonly TenantService $tenantService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant could not be resolved.',
                'code'    => 'TENANT_NOT_FOUND',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$tenant->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant account is inactive or suspended.',
                'code'    => 'TENANT_INACTIVE',
            ], Response::HTTP_FORBIDDEN);
        }

        // Apply tenant's runtime configuration
        $this->tenantService->applyRuntimeConfiguration($tenant);

        // Store tenant in request context for downstream use
        TenantContext::set($tenant);
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenant->id);

        $response = $next($request);

        // Add tenant context headers
        $response->headers->set('X-Tenant-ID', $tenant->id);
        $response->headers->set('X-Tenant-Slug', $tenant->slug);

        // Clean up context after response
        TenantContext::clear();

        return $response;
    }

    /**
     * Resolve the tenant from the request using multiple strategies.
     *
     * @param  Request $request
     * @return \App\Domain\Tenant\Entities\Tenant|null
     */
    private function resolveTenant(Request $request): ?\App\Domain\Tenant\Entities\Tenant
    {
        // Strategy 1: X-Tenant-ID header (UUID)
        if ($tenantId = $request->header('X-Tenant-ID')) {
            return $this->tenantService->getById((string) $tenantId);
        }

        // Strategy 2: Subdomain resolution (tenant.domain.com)
        $host = $request->getHost();
        $appDomain = config('app.domain', 'example.com');

        if (str_ends_with($host, ".{$appDomain}")) {
            $slug = str_replace(".{$appDomain}", '', $host);
            return $this->tenantService->getBySlug($slug);
        }

        // Strategy 3: Full domain lookup
        $tenant = $this->tenantService->getByDomain($host);
        if ($tenant !== null) {
            return $tenant;
        }

        // Strategy 4: Query parameter (development/testing only)
        if (config('app.env') !== 'production' && $slug = $request->query('tenant')) {
            return $this->tenantService->getBySlug((string) $slug);
        }

        return null;
    }
}
