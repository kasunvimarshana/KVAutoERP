<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Domain\Tenant\Entities\Tenant;
use App\Infrastructure\Cache\TenantAwareCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant from the incoming request and binds it
 * into the IoC container so downstream code can use app(Tenant::class).
 *
 * Resolution priority:
 *   1. X-Tenant-ID header
 *   2. X-Tenant-Slug header
 *   3. Subdomain (e.g. acme.app.example.com → slug = acme)
 *   4. tenant_id / tenant_slug query parameter
 */
class TenantMiddleware
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TenantAwareCache $cache,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant could not be resolved. Please provide a valid X-Tenant-ID header.',
            ], 400);
        }

        if (! $tenant->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant is not active.',
            ], 403);
        }

        // Bind tenant into the container for the duration of this request
        app()->instance(Tenant::class, $tenant);

        // Configure cache to use tenant prefix
        $this->cache->setTenant($tenant->id);

        // Attach tenant context to logs
        Log::withContext(['tenant_id' => $tenant->id, 'tenant_slug' => $tenant->slug]);

        return $next($request);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        // 1. Explicit ID header
        if ($tenantId = $request->header('X-Tenant-ID')) {
            return $this->fromCache('id', $tenantId, fn () => $this->tenantRepository->findById($tenantId));
        }

        // 2. Slug header
        if ($slug = $request->header('X-Tenant-Slug')) {
            return $this->fromCache('slug', $slug, fn () => $this->tenantRepository->findBySlug($slug));
        }

        // 3. Subdomain
        $host = $request->getHost();
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            $subdomain = $parts[0];

            return $this->fromCache('slug', $subdomain, fn () => $this->tenantRepository->findBySlug($subdomain));
        }

        // 4. Query parameters
        if ($tenantId = $request->query('tenant_id')) {
            return $this->fromCache('id', $tenantId, fn () => $this->tenantRepository->findById($tenantId));
        }

        if ($slug = $request->query('tenant_slug')) {
            return $this->fromCache('slug', $slug, fn () => $this->tenantRepository->findBySlug($slug));
        }

        return null;
    }

    private function fromCache(string $type, string $value, \Closure $loader): ?Tenant
    {
        $key = "tenant_resolve:{$type}:{$value}";

        /** @var Tenant|null */
        return cache()->remember($key, now()->addMinutes(5), $loader);
    }
}
