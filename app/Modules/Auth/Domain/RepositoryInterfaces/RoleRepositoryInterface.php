<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Auth\Domain\Entities\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;

    public function findByName(string $name, string $guardName = 'api', ?int $tenantId = null): ?Role;

    /** @return Collection<int, Role> */
    public function findByTenantId(?int $tenantId): Collection;

    public function create(array $data): Role;

    public function update(int $id, array $data): ?Role;

    public function delete(int $id): bool;
}
