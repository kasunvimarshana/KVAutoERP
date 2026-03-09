<?php

declare(strict_types=1);

namespace App\Application\Tenant\Services;

use App\Application\Shared\DTOs\PaginationDTO;
use App\Application\Tenant\Commands\CreateTenantCommand;
use App\Application\Tenant\Commands\UpdateTenantCommand;
use App\Application\Tenant\DTOs\TenantDTO;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Infrastructure\RuntimeConfig\RuntimeConfigManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class TenantService implements TenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly RuntimeConfigManager      $runtimeConfigManager,
    ) {}

    public function createTenant(CreateTenantCommand $command): TenantDTO
    {
        $slug = $command->slug ?: Str::slug($command->name);

        if ($this->tenantRepository->exists(['slug' => $slug])) {
            throw new InvalidArgumentException("Tenant with slug '{$slug}' already exists.");
        }

        if ($command->domain && $this->tenantRepository->exists(['domain' => $command->domain])) {
            throw new InvalidArgumentException("Tenant with domain '{$command->domain}' already exists.");
        }

        $data = [
            'name'                 => $command->name,
            'slug'                 => $slug,
            'domain'               => $command->domain,
            'status'               => $command->status,
            'plan'                 => $command->plan,
            'max_users'            => $command->maxUsers,
            'max_organizations'    => $command->maxOrganizations,
            'settings'             => $command->settings ?: null,
            'config'               => $command->config ?: null,
            'database_config'      => $command->databaseConfig ?: null,
            'mail_config'          => $command->mailConfig ?: null,
            'cache_config'         => $command->cacheConfig ?: null,
            'broker_config'        => $command->brokerConfig ?: null,
            'metadata'             => $command->metadata ?: null,
            'trial_ends_at'        => $command->trialEndsAt,
        ];

        $tenant = $this->tenantRepository->create($data);

        Log::info('Tenant created', ['tenant_id' => $tenant->id, 'slug' => $tenant->slug]);

        return TenantDTO::fromEntity($tenant);
    }

    public function updateTenant(string $id, UpdateTenantCommand $command): TenantDTO
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new RuntimeException("Tenant '{$id}' not found.", 404);
        }

        $data = array_filter([
            'name'                 => $command->name,
            'slug'                 => $command->slug,
            'domain'               => $command->domain,
            'status'               => $command->status,
            'plan'                 => $command->plan,
            'max_users'            => $command->maxUsers,
            'max_organizations'    => $command->maxOrganizations,
            'trial_ends_at'        => $command->trialEndsAt,
            'subscription_ends_at' => $command->subscriptionEndsAt,
            'settings'             => $command->settings,
            'config'               => $command->config,
            'metadata'             => $command->metadata,
        ], fn ($v) => $v !== null);

        // Slug uniqueness check (if being changed)
        if (isset($data['slug']) && $data['slug'] !== $tenant->slug) {
            if ($this->tenantRepository->exists(['slug' => $data['slug']])) {
                throw new InvalidArgumentException("Slug '{$data['slug']}' is already taken.");
            }
        }

        // Domain uniqueness check (if being changed)
        if (isset($data['domain']) && $data['domain'] !== $tenant->domain) {
            if ($this->tenantRepository->exists(['domain' => $data['domain']])) {
                throw new InvalidArgumentException("Domain '{$data['domain']}' is already taken.");
            }
        }

        $updated = $this->tenantRepository->update($id, $data);

        return TenantDTO::fromEntity($updated);
    }

    public function suspendTenant(string $id, string $reason): void
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new RuntimeException("Tenant '{$id}' not found.", 404);
        }

        $tenant->suspend($reason);

        Log::warning('Tenant suspended', ['tenant_id' => $id, 'reason' => $reason]);
    }

    public function activateTenant(string $id): void
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new RuntimeException("Tenant '{$id}' not found.", 404);
        }

        $tenant->activate();

        Log::info('Tenant activated', ['tenant_id' => $id]);
    }

    public function deleteTenant(string $id): void
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new RuntimeException("Tenant '{$id}' not found.", 404);
        }

        $this->tenantRepository->delete($id);

        Log::info('Tenant deleted', ['tenant_id' => $id]);
    }

    public function getTenant(string $id): TenantDTO
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new RuntimeException("Tenant '{$id}' not found.", 404);
        }

        return TenantDTO::fromEntity($tenant);
    }

    public function getTenants(PaginationDTO $pagination, array $filters = []): Collection|LengthAwarePaginator
    {
        $options = array_merge($filters, [
            'perPage'       => $pagination->perPage,
            'page'          => $pagination->page,
            'sortBy'        => $pagination->sortBy,
            'sortDirection' => $pagination->sortDir,
        ]);

        return $this->tenantRepository->findAll($options);
    }

    public function updateConfig(string $id, array $config): TenantDTO
    {
        $tenant = $this->tenantRepository->findById($id);

        if ($tenant === null) {
            throw new RuntimeException("Tenant '{$id}' not found.", 404);
        }

        $updated = $this->tenantRepository->updateConfig($id, $config);

        return TenantDTO::fromEntity($updated);
    }

    public function applyRuntimeConfig(string $tenantId): void
    {
        $this->runtimeConfigManager->applyTenantConfig($tenantId);
    }

    public function validateDomain(string $domain): bool
    {
        // Check it's a valid hostname
        if (! filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return false;
        }

        // Check uniqueness
        return ! $this->tenantRepository->exists(['domain' => $domain]);
    }

    public function provisionTenantDatabase(string $tenantId): void
    {
        $tenant = $this->tenantRepository->findById($tenantId);

        if ($tenant === null) {
            throw new RuntimeException("Tenant '{$tenantId}' not found.", 404);
        }

        $dbConfig = $tenant->getDatabaseConnection();

        if ($dbConfig === config('database.default')) {
            // Single-DB mode – no separate database to provision
            Log::info('Tenant uses shared database, skipping provisioning', ['tenant_id' => $tenantId]);

            return;
        }

        // Apply the runtime connection config
        $this->runtimeConfigManager->applyTenantConfig($tenantId);

        // Run migrations on the tenant connection
        \Artisan::call('migrate', [
            '--database' => $dbConfig,
            '--force'    => true,
            '--path'     => 'database/migrations',
        ]);

        Log::info('Tenant database provisioned', ['tenant_id' => $tenantId, 'connection' => $dbConfig]);
    }
}
