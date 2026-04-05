<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\RepositoryInterfaces;

use Modules\Tenant\Domain\Entities\Tenant;

interface TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    public function create(array $data): Tenant;

    public function update(int $id, array $data): ?Tenant;

    public function delete(int $id): bool;

    /** @return Tenant[] */
    public function all(): array;
}
