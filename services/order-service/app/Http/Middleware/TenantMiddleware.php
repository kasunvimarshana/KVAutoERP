<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID');

        if (empty($tenantId)) {
            return response()->json([
                'message' => 'X-Tenant-ID header is required.',
            ], 400);
        }

        // Store tenant in request attributes for downstream use
        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }
}
