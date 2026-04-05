<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\Tenant;

interface TenantRepositoryInterface
{
    public function findById(string $id): ?Tenant;
    public function findBySlug(string $slug): ?Tenant;
    public function create(array $data): Tenant;
    public function update(string $id, array $data): Tenant;
    public function delete(string $id): bool;
    public function all(): Collection;
}
