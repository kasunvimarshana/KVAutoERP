<?php

declare(strict_types=1);

namespace App\Application\Tenant\Services;

use App\Application\Shared\DTOs\PaginationDTO;
use App\Application\Tenant\Commands\CreateTenantCommand;
use App\Application\Tenant\Commands\UpdateTenantCommand;
use App\Application\Tenant\DTOs\TenantDTO;

interface TenantServiceInterface
{
    public function createTenant(CreateTenantCommand $command): TenantDTO;

    public function updateTenant(string $id, UpdateTenantCommand $command): TenantDTO;

    public function suspendTenant(string $id, string $reason): void;

    public function activateTenant(string $id): void;

    public function deleteTenant(string $id): void;

    public function getTenant(string $id): TenantDTO;

    public function getTenants(PaginationDTO $pagination, array $filters = []): mixed;

    public function updateConfig(string $id, array $config): TenantDTO;

    public function applyRuntimeConfig(string $tenantId): void;

    public function validateDomain(string $domain): bool;

    public function provisionTenantDatabase(string $tenantId): void;
}
