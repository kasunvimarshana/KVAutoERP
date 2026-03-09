<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\Services;

use App\Core\Abstracts\Services\BaseService;
use App\Core\Exceptions\TenantException;
use App\Infrastructure\Config\RuntimeConfigurationService;
use App\Modules\Tenant\Infrastructure\Repositories\TenantRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * TenantService
 *
 * Handles all tenant lifecycle operations: creation, onboarding,
 * configuration loading, and domain resolution.
 */
class TenantService extends BaseService
{
    public function __construct(
        private readonly TenantRepository $tenantRepository,
        private readonly RuntimeConfigurationService $runtimeConfig
    ) {}

    // -------------------------------------------------------------------------
    //  Queries
    // -------------------------------------------------------------------------

    public function list(
        array $filters = [],
        array $sort = [],
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection {
        return $this->tenantRepository->all(
            filters: $filters,
            sort:    $sort,
            perPage: $perPage,
            page:    $page
        );
    }

    public function findById(int|string $id): Model
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new TenantException("Tenant [{$id}] not found.");
        }

        return $tenant;
    }

    public function findBySlug(string $slug): Model
    {
        $tenant = $this->tenantRepository->findBySlug($slug);

        if ($tenant === null) {
            throw new TenantException("Tenant with slug [{$slug}] not found.");
        }

        return $tenant;
    }

    // -------------------------------------------------------------------------
    //  Mutations
    // -------------------------------------------------------------------------

    public function create(array $data): Model
    {
        $data['slug'] ??= Str::slug($data['name']);

        return $this->tenantRepository->create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        return $this->tenantRepository->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->tenantRepository->delete($id);
    }

    // -------------------------------------------------------------------------
    //  Runtime configuration
    // -------------------------------------------------------------------------

    /**
     * Load and apply the tenant's runtime configurations.
     */
    public function loadConfigurations(int|string $tenantId): void
    {
        $this->runtimeConfig->loadTenantConfigurations($tenantId);
    }

    /**
     * Resolve the active tenant from the current HTTP request.
     *
     * Resolution order:
     *  1. X-Tenant-ID header
     *  2. Subdomain (e.g. acme.saas.example.com → slug = "acme")
     *  3. Host header matching a stored domain
     *
     * @throws TenantException when no matching active tenant is found
     */
    public function resolveFromRequest(\Illuminate\Http\Request $request): Model
    {
        // 1. X-Tenant-ID header
        $tenantId = $request->header('X-Tenant-ID');
        if ($tenantId) {
            $tenant = $this->tenantRepository->findById($tenantId);
            if ($tenant && $tenant->is_active) {
                return $tenant;
            }
        }

        // 2. Subdomain
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        $tenant = $this->tenantRepository->findBySlug($subdomain);
        if ($tenant && $tenant->is_active) {
            return $tenant;
        }

        // 3. Full domain match
        $tenant = $this->tenantRepository->findByDomain($host);
        if ($tenant && $tenant->is_active) {
            return $tenant;
        }

        throw new TenantException("No active tenant could be resolved for host [{$host}].");
    }
}
