<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$user->tenant_id) {
            return response()->json(['message' => 'No tenant associated with this user'], 403);
        }

        // Set tenant context on request
        $request->merge(['current_tenant_id' => $user->tenant_id]);
        app()->instance('current_tenant_id', $user->tenant_id);

        // Check tenant_id in request matches user's tenant (for non-admin users)
        if (!$user->hasRole('super-admin') && $request->has('tenant_id')) {
            if ((int) $request->input('tenant_id') !== (int) $user->tenant_id) {
                return response()->json(['message' => 'Access denied to this tenant'], 403);
            }
        }

        return $next($request);
    }
}
