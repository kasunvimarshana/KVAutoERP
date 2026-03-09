<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Exceptions\TenantException;
use App\Modules\Tenant\Application\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantMiddleware
 *
 * Resolves the active tenant from the request and:
 *  1. Loads runtime configurations for the tenant
 *  2. Stores the tenant on the request for downstream use
 *
 * Apply to all routes that must be tenant-aware.
 */
class TenantMiddleware
{
    public function __construct(
        private readonly TenantService $tenantService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tenant = $this->tenantService->resolveFromRequest($request);

            // Store on request so controllers/services can access it
            $request->attributes->set('tenant', $tenant);
            app()->instance('current.tenant', $tenant);

            // Apply tenant runtime configurations (DB, mail, cache, etc.)
            $this->tenantService->loadConfigurations($tenant->id);

        } catch (TenantException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }

        return $next($request);
    }
}
