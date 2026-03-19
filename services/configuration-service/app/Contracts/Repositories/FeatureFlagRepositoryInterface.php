<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\FeatureFlag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FeatureFlagRepositoryInterface
{
    public function findById(string $id): ?FeatureFlag;

    public function findByKey(string $tenantId, string $flagKey): ?FeatureFlag;

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    public function findAllByTenant(string $tenantId): Collection;

    public function create(array $data): FeatureFlag;

    public function update(string $id, array $data): FeatureFlag;

    public function delete(string $id): bool;

    public function toggle(string $id): FeatureFlag;

    public function existsByKey(string $tenantId, string $flagKey): bool;
}
