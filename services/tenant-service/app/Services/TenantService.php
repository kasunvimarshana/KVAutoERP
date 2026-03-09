<?php

declare(strict_types=1);

namespace App\Services;

use App\Application\Tenant\Commands\CreateTenantCommand;
use App\Application\Tenant\Commands\DeleteTenantCommand;
use App\Application\Tenant\Commands\UpdateTenantCommand;
use App\Application\Tenant\Commands\UpdateTenantConfigCommand;
use App\Application\Tenant\Handlers\CreateTenantCommandHandler;
use App\Application\Tenant\Handlers\DeleteTenantCommandHandler;
use App\Application\Tenant\Handlers\UpdateTenantCommandHandler;
use App\Application\Tenant\Handlers\UpdateTenantConfigCommandHandler;
use App\Application\Tenant\Queries\GetTenantConfigQuery;
use App\Application\Tenant\Queries\GetTenantQuery;
use App\Application\Tenant\Queries\ListTenantsQuery;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Shared\Base\BaseService;
use App\Shared\Contracts\WebhookInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Psr\Log\LoggerInterface;

/**
 * Tenant Application Service.
 *
 * Thin orchestration layer between HTTP controllers and domain command/query
 * handlers. Delegates all business logic to the appropriate handler.
 */
final class TenantService extends BaseService
{
    public function __construct(
        TenantRepositoryInterface $repository,
        private readonly CreateTenantCommandHandler $createHandler,
        private readonly UpdateTenantCommandHandler $updateHandler,
        private readonly DeleteTenantCommandHandler $deleteHandler,
        private readonly UpdateTenantConfigCommandHandler $configHandler,
        ?WebhookInterface $webhook = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($repository, $webhook, $logger);
    }

    /**
     * Create a new tenant and provision its resources.
     *
     * @param  CreateTenantCommand  $command
     * @return array<string, mixed>
     */
    public function createTenant(CreateTenantCommand $command): array
    {
        return $this->createHandler->handle($command);
    }

    /**
     * Update an existing tenant's attributes.
     *
     * @param  UpdateTenantCommand  $command
     * @return array<string, mixed>
     */
    public function updateTenant(UpdateTenantCommand $command): array
    {
        return $this->updateHandler->handle($command);
    }

    /**
     * Soft-delete a tenant.
     *
     * @param  DeleteTenantCommand  $command
     */
    public function deleteTenant(DeleteTenantCommand $command): void
    {
        $this->deleteHandler->handle($command);
    }

    /**
     * Return a single tenant by UUID.
     *
     * @param  string  $tenantId
     * @return array<string, mixed>|null
     */
    public function getTenant(string $tenantId): ?array
    {
        return $this->repository->findById($tenantId);
    }

    /**
     * Return a paginated list of tenants.
     *
     * @param  ListTenantsQuery  $query
     * @return array<int, array>|LengthAwarePaginator
     */
    public function listTenants(ListTenantsQuery $query): array|LengthAwarePaginator
    {
        $filters = $query->filters;

        if (!$query->includeInactive) {
            $filters['is_active'] = true;
        }

        return $this->repository->findAll(
            filters: $filters,
            sorts: $query->sorts,
            perPage: $query->perPage,
            page: $query->page,
        );
    }

    /**
     * Persist a single configuration key for a tenant.
     *
     * @param  UpdateTenantConfigCommand  $command
     */
    public function updateTenantConfig(UpdateTenantConfigCommand $command): void
    {
        $this->configHandler->handle($command);
    }

    /**
     * Return configuration entries for a tenant.
     *
     * @param  GetTenantConfigQuery  $query
     * @return array<int, array<string, mixed>>
     */
    public function getTenantConfig(GetTenantConfigQuery $query): array
    {
        if ($query->configKey !== null) {
            // Return a single config entry as a 1-element array.
            /** @var \App\Infrastructure\Persistence\Models\TenantConfiguration|null $model */
            $model = \App\Infrastructure\Persistence\Models\TenantConfiguration::query()
                ->where('tenant_id', $query->tenantId)
                ->where('config_key', $query->configKey)
                ->when($query->environment, fn ($q) => $q->where('environment', $query->environment))
                ->first();

            return $model ? [$model->toArray()] : [];
        }

        // Return all config entries for the tenant.
        return \App\Infrastructure\Persistence\Models\TenantConfiguration::query()
            ->where('tenant_id', $query->tenantId)
            ->when($query->environment, fn ($q) => $q->where('environment', $query->environment))
            ->get()
            ->toArray();
    }
}
