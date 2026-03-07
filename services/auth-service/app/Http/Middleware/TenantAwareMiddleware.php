<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantAwareMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID');

        if (empty($tenantId)) {
            return response()->json([
                'message' => 'X-Tenant-ID header is required.',
            ], 400);
        }

        // Validate the authenticated user belongs to the claimed tenant
        $user = $request->user();
        if ($user && $user->tenant_id !== $tenantId) {
            return response()->json([
                'message' => 'Tenant mismatch: token tenant does not match X-Tenant-ID header.',
            ], 403);
        }

        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }
}
