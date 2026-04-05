<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Auth\Domain\Entities\Role;

interface RoleRepositoryInterface
{
    public function findById(string $id): ?Role;
    public function findBySlug(string $slug, string $tenantId): ?Role;
    public function allByTenant(string $tenantId): Collection;
    public function create(array $data): Role;
    public function update(string $id, array $data): Role;
    public function delete(string $id): bool;
}
