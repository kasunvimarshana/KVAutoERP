<?php

namespace App\Http\Middleware;

use App\Core\Tenant\TenantManager;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveTenantKey($request);

        if (!$key) {
            return response()->json(['message' => 'Tenant not identified.'], 400);
        }

        $tenant = Tenant::where('key', $key)->where('is_active', true)->first();

        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found or inactive.'], 404);
        }

        $this->tenantManager->setTenant($tenant);

        return $next($request);
    }

    protected function resolveTenantKey(Request $request): ?string
    {
        // 1. Explicit header
        if ($request->hasHeader('X-Tenant')) {
            return $request->header('X-Tenant');
        }

        // 2. From authenticated user's tenant
        $user = $request->user();
        if ($user && $user->tenant) {
            return $user->tenant->key;
        }

        // 3. Subdomain
        $host  = $request->getHost();
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            return $parts[0];
        }

        // 4. Query param (local / test only)
        if (app()->environment('local', 'testing') && $request->has('tenant')) {
            return $request->query('tenant');
        }

        return null;
    }
}
