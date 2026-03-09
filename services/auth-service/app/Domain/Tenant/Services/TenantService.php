<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Repositories\Interfaces\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tenant Service.
 *
 * Handles all tenant management business logic including runtime configuration,
 * dynamic database connections, and tenant lifecycle management.
 */
class TenantService
{
    /**
     * Cache TTL for tenant configuration (seconds).
     */
    private const CONFIG_CACHE_TTL = 3600;

    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {}

    // =========================================================================
    // Tenant CRUD
    // =========================================================================

    /**
     * List all tenants with optional filtering and pagination.
     *
     * @param  array<string, mixed>                     $params
     * @return LengthAwarePaginator|Collection<int, Tenant>
     */
    public function list(array $params = []): LengthAwarePaginator|Collection
    {
        return $this->tenantRepository->all($params);
    }

    /**
     * Get a tenant by ID.
     *
     * @param  string $id
     * @return Tenant
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(string $id): Tenant
    {
        return $this->tenantRepository->find($id)
            ?? throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Tenant [{$id}] not found.");
    }

    /**
     * Get a tenant by its slug.
     *
     * @param  string $slug
     * @return Tenant|null
     */
    public function getBySlug(string $slug): ?Tenant
    {
        return Cache::remember(
            "tenant:slug:{$slug}",
            self::CONFIG_CACHE_TTL,
            fn () => $this->tenantRepository->findBySlug($slug),
        );
    }

    /**
     * Get a tenant by domain.
     *
     * @param  string $domain
     * @return Tenant|null
     */
    public function getByDomain(string $domain): ?Tenant
    {
        return Cache::remember(
            "tenant:domain:{$domain}",
            self::CONFIG_CACHE_TTL,
            fn () => $this->tenantRepository->findByDomain($domain),
        );
    }

    /**
     * Create a new tenant with its isolated infrastructure.
     *
     * @param  array<string, mixed> $data
     * @return Tenant
     */
    public function create(array $data): Tenant
    {
        $data['slug']          = $data['slug'] ?? Str::slug($data['name']);
        $data['database_name'] = $data['database_name'] ?? "tenant_{$data['slug']}";
        $data['status']        = $data['status'] ?? 'active';

        return DB::transaction(function () use ($data): Tenant {
            $tenant = $this->tenantRepository->create($data);
            $this->clearTenantCache($tenant);

            return $tenant;
        });
    }

    /**
     * Update tenant details.
     *
     * @param  string               $id
     * @param  array<string, mixed> $data
     * @return Tenant
     */
    public function update(string $id, array $data): Tenant
    {
        return DB::transaction(function () use ($id, $data): Tenant {
            $tenant = $this->tenantRepository->update($id, $data);
            $this->clearTenantCache($tenant);

            return $tenant;
        });
    }

    /**
     * Delete a tenant.
     *
     * @param  string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $tenant = $this->getById($id);

        return DB::transaction(function () use ($id, $tenant): bool {
            $result = $this->tenantRepository->delete($id);
            $this->clearTenantCache($tenant);

            return $result;
        });
    }

    // =========================================================================
    // Runtime Configuration
    // =========================================================================

    /**
     * Update tenant's runtime configuration without application restart.
     *
     * Allows dynamic changes to database connections, email settings,
     * cache drivers, message brokers, and other environment settings.
     *
     * @param  string               $tenantId
     * @param  array<string, mixed> $config   Dot-notation configuration values
     * @return Tenant
     */
    public function updateRuntimeConfiguration(string $tenantId, array $config): Tenant
    {
        $tenant = $this->tenantRepository->updateConfiguration($tenantId, $config);

        // Apply the configuration immediately to the running application
        $this->applyRuntimeConfiguration($tenant);

        $this->clearTenantCache($tenant);

        return $tenant;
    }

    /**
     * Apply a tenant's stored configuration to the running Laravel application.
     *
     * This enables runtime configuration changes without redeployment.
     *
     * @param  Tenant $tenant
     * @return void
     */
    public function applyRuntimeConfiguration(Tenant $tenant): void
    {
        $config = $tenant->configuration ?? [];

        // Apply database connection config
        if (isset($config['database'])) {
            Config::set("database.connections.tenant_{$tenant->slug}", array_merge(
                Config::get('database.connections.mysql', []),
                $config['database'],
                ['database' => $tenant->database_name],
            ));
        }

        // Apply mail configuration
        if (isset($config['mail'])) {
            foreach ($config['mail'] as $key => $value) {
                Config::set("mail.{$key}", $value);
            }
        }

        // Apply cache driver
        if (isset($config['cache']['driver'])) {
            Config::set('cache.default', $config['cache']['driver']);
        }

        // Apply queue connection
        if (isset($config['queue']['connection'])) {
            Config::set('queue.default', $config['queue']['connection']);
        }

        // Apply message broker settings
        if (isset($config['message_broker'])) {
            Config::set('services.message_broker', $config['message_broker']);
        }

        // Apply custom service URLs for cross-service communication
        if (isset($config['services'])) {
            foreach ($config['services'] as $service => $serviceConfig) {
                Config::set("services.{$service}", $serviceConfig);
            }
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Clear all cached data for a tenant.
     *
     * @param  Tenant $tenant
     * @return void
     */
    private function clearTenantCache(Tenant $tenant): void
    {
        Cache::forget("tenant:slug:{$tenant->slug}");
        Cache::forget("tenant:domain:{$tenant->domain}");
        Cache::forget("tenant:config:{$tenant->id}");
    }
}
