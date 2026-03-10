<?php

namespace App\Repositories\Contracts;

use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TenantRepositoryInterface
{
    public function all(array $filters = [], array $params = []): LengthAwarePaginator|Collection;
    public function findById(string $id): ?Tenant;
    public function findBySlug(string $slug): ?Tenant;
    public function create(array $data): Tenant;
    public function update(string $id, array $data): Tenant;
    public function delete(string $id): bool;
}
