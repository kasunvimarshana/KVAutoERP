<?php

declare(strict_types=1);

namespace KvSaas\Contracts\User;

use KvSaas\Contracts\User\Dto\TenantConfigDto;
use KvSaas\Contracts\User\Dto\TenantDto;
use KvSaas\Contracts\User\Dto\TenantHierarchyDto;

/**
 * Contract for tenant resolution and runtime configuration.
 */
interface TenantProviderInterface
{
    public function findById(string $tenantId): ?TenantDto;

    public function getHierarchy(string $tenantId): TenantHierarchyDto;

    public function getConfiguration(string $tenantId): TenantConfigDto;

    public function getIamProvider(string $tenantId): string;

    public function isActive(string $tenantId): bool;
}
