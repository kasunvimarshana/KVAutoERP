<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\FeatureFlagRepositoryInterface;
use App\Models\FeatureFlag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FeatureFlagRepository implements FeatureFlagRepositoryInterface
{
    public function findById(string $id): ?FeatureFlag
    {
        return FeatureFlag::find($id);
    }

    public function findByKey(string $tenantId, string $flagKey): ?FeatureFlag
    {
        return FeatureFlag::forTenant($tenantId)
            ->where('flag_key', $flagKey)
            ->first();
    }

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return FeatureFlag::forTenant($tenantId)
            ->orderBy('flag_key')
            ->paginate($perPage);
    }

    public function findAllByTenant(string $tenantId): Collection
    {
        return FeatureFlag::forTenant($tenantId)->orderBy('flag_key')->get();
    }

    public function create(array $data): FeatureFlag
    {
        return FeatureFlag::create($data);
    }

    public function update(string $id, array $data): FeatureFlag
    {
        $flag = FeatureFlag::findOrFail($id);
        $flag->update($data);

        return $flag->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) FeatureFlag::findOrFail($id)->delete();
    }

    public function toggle(string $id): FeatureFlag
    {
        $flag = FeatureFlag::findOrFail($id);
        $flag->update(['is_enabled' => ! $flag->is_enabled]);

        return $flag->fresh();
    }

    public function existsByKey(string $tenantId, string $flagKey): bool
    {
        return FeatureFlag::forTenant($tenantId)
            ->where('flag_key', $flagKey)
            ->exists();
    }
}
