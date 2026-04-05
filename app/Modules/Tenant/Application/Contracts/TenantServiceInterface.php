<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\Tenant;

interface TenantServiceInterface
{
    public function createTenant(array $data): Tenant;
    public function updateTenant(string $id, array $data): Tenant;
    public function suspendTenant(string $id): Tenant;
    public function activateTenant(string $id): Tenant;
    public function getTenant(string $id): Tenant;
    public function getAll(): Collection;
}
