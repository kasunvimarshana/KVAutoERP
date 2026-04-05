<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\Tenant;

interface TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    public function findByDomain(string $domain): ?Tenant;

    /** @return Collection<int, Tenant> */
    public function all(): Collection;

    /** @return Collection<int, Tenant> */
    public function findActive(): Collection;

    public function create(array $data): Tenant;

    public function update(int $id, array $data): ?Tenant;

    public function delete(int $id): bool;
}
