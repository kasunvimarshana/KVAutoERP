<?php

declare(strict_types=1);

namespace App\Domain\Organization\Repositories;

use App\Domain\Organization\Entities\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrganizationRepositoryInterface
{
    public function findById(string $id): ?Organization;

    public function findBySlug(string $tenantId, string $slug): ?Organization;

    /**
     * @return Collection<int, Organization>|LengthAwarePaginator
     */
    public function findAll(array $filters = []): Collection|LengthAwarePaginator;

    /**
     * @return Collection<int, Organization>
     */
    public function findByTenant(string $tenantId, array $filters = []): Collection;

    /**
     * @return Collection<int, Organization>
     */
    public function findRoots(string $tenantId): Collection;

    /**
     * @return Collection<int, Organization>
     */
    public function findChildren(string $parentId): Collection;

    public function create(array $data): Organization;

    public function update(string $id, array $data): ?Organization;

    public function delete(string $id): bool;

    public function exists(array $conditions): bool;

    public function count(array $filters = []): int;
}
