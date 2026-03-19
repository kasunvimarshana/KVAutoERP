<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\FeatureFlagRepositoryInterface;
use App\Contracts\Services\FeatureFlagServiceInterface;
use App\DTOs\FeatureFlagDto;
use App\Exceptions\ConfigurationException;
use App\Models\FeatureFlag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class FeatureFlagService implements FeatureFlagServiceInterface
{
    public function __construct(
        private readonly FeatureFlagRepositoryInterface $flagRepository,
    ) {}

    public function isEnabled(string $tenantId, string $flagKey, array $context = []): bool
    {
        $cacheKey = "feature:{$tenantId}:{$flagKey}";
        $ttl = (int) config('configuration.feature_flag_cache_ttl', 60);

        $flag = Cache::remember($cacheKey, $ttl, function () use ($tenantId, $flagKey) {
            return $this->flagRepository->findByKey($tenantId, $flagKey);
        });

        if ($flag === null) {
            return false;
        }

        return $flag->isActiveForContext($context);
    }

    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->flagRepository->findByTenant($tenantId, $perPage);
    }

    public function create(FeatureFlagDto $dto): FeatureFlag
    {
        if ($this->flagRepository->existsByKey($dto->tenantId, $dto->flagKey)) {
            throw new ConfigurationException(
                "Feature flag '{$dto->flagKey}' already exists for this tenant.",
                409,
            );
        }

        return $this->flagRepository->create($dto->toArray());
    }

    public function update(string $id, FeatureFlagDto $dto): FeatureFlag
    {
        $this->findById($id);
        $flag = $this->flagRepository->update($id, $dto->toArray());
        $this->flushFlagCache($flag->tenant_id, $flag->flag_key);

        return $flag;
    }

    public function delete(string $id): void
    {
        $flag = $this->findById($id);
        $this->flagRepository->delete($id);
        $this->flushFlagCache($flag->tenant_id, $flag->flag_key);
    }

    public function toggle(string $id): FeatureFlag
    {
        $flag = $this->findById($id);
        $updated = $this->flagRepository->toggle($id);
        $this->flushFlagCache($flag->tenant_id, $flag->flag_key);

        return $updated;
    }

    public function findById(string $id): FeatureFlag
    {
        $flag = $this->flagRepository->findById($id);

        if ($flag === null) {
            throw new ConfigurationException("Feature flag not found with ID: {$id}", 404);
        }

        return $flag;
    }

    private function flushFlagCache(string $tenantId, string $flagKey): void
    {
        Cache::forget("feature:{$tenantId}:{$flagKey}");
    }
}
