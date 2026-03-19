<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\FeatureFlagDto;
use App\Models\FeatureFlag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FeatureFlagServiceInterface
{
    /**
     * Check whether a specific feature flag is enabled for a tenant and user context.
     */
    public function isEnabled(string $tenantId, string $flagKey, array $context = []): bool;

    /**
     * Paginated list of all flags for a tenant.
     */
    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new feature flag.
     */
    public function create(FeatureFlagDto $dto): FeatureFlag;

    /**
     * Update an existing feature flag.
     */
    public function update(string $id, FeatureFlagDto $dto): FeatureFlag;

    /**
     * Delete a feature flag (soft-delete).
     */
    public function delete(string $id): void;

    /**
     * Toggle the enabled state of a feature flag.
     */
    public function toggle(string $id): FeatureFlag;

    /**
     * Find a feature flag by its ID.
     */
    public function findById(string $id): FeatureFlag;
}
