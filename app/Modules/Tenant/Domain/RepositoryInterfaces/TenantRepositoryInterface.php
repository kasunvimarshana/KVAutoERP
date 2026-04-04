<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\RepositoryInterfaces;

use Modules\Tenant\Domain\Entities\Tenant;

interface TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    public function findAll(int $page = 1, int $perPage = 15): array;

    public function save(Tenant $tenant): Tenant;

    public function delete(int $id): void;
}
