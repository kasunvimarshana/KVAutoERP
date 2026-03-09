<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TenantRepositoryInterface
{
    public function findById(string $id): ?Tenant;

    public function findByDomain(string $domain): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    /**
     * @return Collection<int, Tenant>|LengthAwarePaginator
     */
    public function findAll(array $filters = []): Collection|LengthAwarePaginator;

    public function create(array $data): Tenant;

    public function update(string $id, array $data): ?Tenant;

    public function delete(string $id): bool;

    /**
     * @return Collection<int, Tenant>
     */
    public function findActive(): Collection;

    public function updateConfig(string $id, array $config): ?Tenant;

    public function updateDatabaseConfig(string $id, array $databaseConfig): ?Tenant;

    public function updateMailConfig(string $id, array $mailConfig): ?Tenant;

    public function updateCacheConfig(string $id, array $cacheConfig): ?Tenant;

    public function updateBrokerConfig(string $id, array $brokerConfig): ?Tenant;

    public function exists(array $conditions): bool;

    public function count(array $filters = []): int;
}
