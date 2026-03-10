<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID')
            ?? $request->input('tenant_id')
            ?? config('tenant.id');

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant identification required.',
                'error_code' => 'TENANT_REQUIRED',
            ], 400);
        }

        App::instance('current.tenant.id', $tenantId);
        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }
}
