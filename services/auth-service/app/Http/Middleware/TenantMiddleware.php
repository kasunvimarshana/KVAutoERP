<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        if (!$tenant->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant is inactive or suspended.',
            ], 403);
        }

        app()->instance('tenant', $tenant);
        app()->instance('tenant.id', $tenant->id);

        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenant->id);

        return $next($request);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        $tenantIdentifier = $this->extractTenantIdentifier($request);

        if ($tenantIdentifier === null) {
            return null;
        }

        return Tenant::where(function ($query) use ($tenantIdentifier): void {
            $query->where('id', $tenantIdentifier)
                  ->orWhere('slug', $tenantIdentifier);
        })->first();
    }

    private function extractTenantIdentifier(Request $request): ?string
    {
        // 1. Check X-Tenant-ID header (highest priority)
        $headerTenant = $request->header('X-Tenant-ID');
        if (!empty($headerTenant)) {
            return $headerTenant;
        }

        // 2. Check query parameter
        $queryTenant = $request->query('tenant_id');
        if (!empty($queryTenant)) {
            return $queryTenant;
        }

        // 3. Check subdomain (e.g., acme.example.com)
        $host = $request->getHost();
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            if ($subdomain !== 'www' && $subdomain !== 'api') {
                return $subdomain;
            }
        }

        // 4. Check request body (for login / register endpoints)
        $bodyTenant = $request->input('tenant_id');
        if (!empty($bodyTenant)) {
            return $bodyTenant;
        }

        return null;
    }
}
