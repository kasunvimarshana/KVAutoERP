<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\Tenant;

interface TenantServiceInterface
{
    public function create(array $data): Tenant;

    public function update(int $id, array $data): Tenant;

    public function delete(int $id): bool;

    public function findById(int $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    public function findByDomain(string $domain): ?Tenant;

    public function list(): Collection;
}
