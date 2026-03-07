<?php

namespace App\Application\Services;

use App\Domain\Tenant\Entities\Tenant;
use App\Infrastructure\Repositories\EloquentTenantRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class TenantService
{
    public function __construct(
        private EloquentTenantRepository $repository,
        private TenantConfigManager      $configManager,
        private LoggerInterface          $logger,
    ) {}

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Create a new tenant with generated credentials and a trial period.
     */
    public function createTenant(array $data): Tenant
    {
        $data['id']     = Str::uuid()->toString();
        $data['slug']   = $data['slug'] ?? Str::slug($data['name']);
        $data['status'] = Tenant::STATUS_TRIAL;
        $data['plan']   = $data['plan'] ?? config('tenant.default_plan', 'free');

        $trialDays            = (int) config('tenant.trial_days', 14);
        $data['trial_ends_at'] = now()->addDays($trialDays);

        // Merge any injected db_config with sane defaults
        if (!empty($data['db_config'])) {
            $data['db_config'] = $this->normaliseDbConfig($data['db_config']);
        }

        $tenant = $this->repository->create($data);

        $this->logger->info('Tenant created', [
            'tenant_id' => $tenant->id,
            'slug'      => $tenant->slug,
        ]);

        return $tenant;
    }

    /**
     * Update mutable tenant fields and invalidate cached config.
     */
    public function updateTenant(string $id, array $data): Tenant
    {
        // Prevent accidental overwrite of the primary key
        unset($data['id']);

        if (isset($data['db_config'])) {
            $data['db_config'] = $this->normaliseDbConfig($data['db_config']);
        }

        $tenant = $this->repository->update($id, $data);
        $this->configManager->clearTenantConfig($id);

        $this->logger->info('Tenant updated', ['tenant_id' => $id]);

        return $tenant;
    }

    /**
     * Soft-delete a tenant and wipe its cached config.
     */
    public function deleteTenant(string $id): bool
    {
        $result = $this->repository->delete($id);
        $this->configManager->clearTenantConfig($id);

        $this->logger->info('Tenant deleted', ['tenant_id' => $id]);

        return $result;
    }

    /**
     * Retrieve a single tenant by ID.
     */
    public function getTenant(string $id): Tenant
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * List tenants with optional search/status filters and pagination.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function listTenants(array $params = []): mixed
    {
        return $this->repository->paginateOrGet($params);
    }

    // -------------------------------------------------------------------------
    // Status transitions
    // -------------------------------------------------------------------------

    /**
     * Activate a tenant and reload its runtime config.
     */
    public function activateTenant(string $id): Tenant
    {
        $tenant = $this->repository->update($id, ['status' => Tenant::STATUS_ACTIVE]);
        $this->configManager->refreshTenantConfig($id);

        $this->logger->info('Tenant activated', ['tenant_id' => $id]);

        return $tenant;
    }

    /**
     * Suspend a tenant and clear its runtime config.
     */
    public function suspendTenant(string $id): Tenant
    {
        $tenant = $this->repository->update($id, ['status' => Tenant::STATUS_SUSPENDED]);
        $this->configManager->clearTenantConfig($id);

        $this->logger->warning('Tenant suspended', ['tenant_id' => $id]);

        return $tenant;
    }

    // -------------------------------------------------------------------------
    // Config management
    // -------------------------------------------------------------------------

    /**
     * Return the resolved (cached) tenant config array.
     */
    public function getTenantConfig(string $id): array
    {
        return $this->configManager->getTenantConfig($id);
    }

    /**
     * Persist updated config fields to the tenant record and refresh the cache.
     */
    public function updateTenantConfig(string $id, array $configData): Tenant
    {
        $allowed = ['settings', 'db_config', 'cache_config', 'mail_config', 'broker_config'];
        $updates = array_intersect_key($configData, array_flip($allowed));

        if (isset($updates['db_config'])) {
            $updates['db_config'] = $this->normaliseDbConfig($updates['db_config']);
        }

        $tenant = $this->repository->update($id, $updates);
        $this->configManager->refreshTenantConfig($id);

        $this->logger->info('Tenant config updated', ['tenant_id' => $id]);

        return $tenant;
    }

    /**
     * Invalidate and reload the cached config for a tenant.
     */
    public function refreshTenantConfig(string $id): void
    {
        $this->configManager->refreshTenantConfig($id);
    }

    // -------------------------------------------------------------------------
    // Domain / credential helpers
    // -------------------------------------------------------------------------

    /**
     * Confirm that the given domain is not already taken by another tenant.
     */
    public function validateTenantDomain(string $domain, ?string $excludeId = null): bool
    {
        $existing = $this->repository->findByDomain($domain);

        if ($existing === null) {
            return true;
        }

        return $excludeId !== null && $existing->id === $excludeId;
    }

    /**
     * Generate a random DB password and return a ready-to-store db_config snippet.
     */
    public function generateTenantCredentials(string $tenantSlug): array
    {
        return [
            'username' => 'tenant_' . Str::slug($tenantSlug, '_'),
            'password' => Str::random(32),
            'database' => 'tenant_' . Str::slug($tenantSlug, '_'),
        ];
    }

    /**
     * Find a tenant by its slug.
     */
    public function findBySlug(string $slug): ?Tenant
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * Find a tenant by its custom domain.
     */
    public function findByDomain(string $domain): ?Tenant
    {
        return $this->repository->findByDomain($domain);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function normaliseDbConfig(array $dbConfig): array
    {
        return array_merge([
            'driver'    => 'mysql',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict'    => true,
        ], $dbConfig);
    }
}
