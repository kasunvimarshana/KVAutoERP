<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

interface TenantRepositoryInterface
{
    public function findById(string $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    public function create(array $data): Tenant;

    public function update(string $id, array $data): Tenant;

    public function delete(string $id): bool;

    public function isActive(string $tenantId): bool;

    public function getFeatureFlags(string $tenantId): array;

    public function setFeatureFlag(string $tenantId, string $flag, mixed $value): void;

    public function getConfiguration(string $tenantId, string $key, mixed $default = null): mixed;

    public function setConfiguration(string $tenantId, string $key, mixed $value): void;

    public function getTokenLifetimes(string $tenantId): array;

    public function all(): Collection;
}
