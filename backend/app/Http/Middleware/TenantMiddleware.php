<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Prefer tenant_id from the authenticated JWT (set by AuthenticateWithKeycloak)
        // Fall back to the X-Tenant-ID header as secondary source
        $tenantId = $request->get('tenant_id')
            ?? $request->header('X-Tenant-ID');

        if (!$tenantId) {
            return response()->json(['error' => 'Tenant context required'], 400);
        }

        app()->instance('tenant_id', $tenantId);

        return $next($request);
    }
}
