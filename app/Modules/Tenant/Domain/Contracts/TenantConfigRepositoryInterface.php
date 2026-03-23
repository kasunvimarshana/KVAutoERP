<?php

namespace Modules\Tenant\Domain\Contracts;

interface TenantConfigRepositoryInterface
{
    public function find(int $id): ?TenantConfigInterface;
    public function findByDomain(string $domain): ?TenantConfigInterface;
}
