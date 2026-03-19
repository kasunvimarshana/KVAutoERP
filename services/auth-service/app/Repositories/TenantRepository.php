<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

class TenantRepository implements TenantRepositoryInterface
{
    public function findById(string $id): ?Tenant
    {
        return Tenant::find($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return Tenant::where('slug', $slug)->first();
    }

    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function update(string $id, array $data): Tenant
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update($data);
        return $tenant->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) Tenant::findOrFail($id)->delete();
    }

    public function isActive(string $tenantId): bool
    {
        return Tenant::where('id', $tenantId)->where('is_active', true)->exists();
    }

    public function getFeatureFlags(string $tenantId): array
    {
        $tenant = $this->findById($tenantId);
        return $tenant?->feature_flags ?? [];
    }

    public function setFeatureFlag(string $tenantId, string $flag, mixed $value): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        $flags = $tenant->feature_flags ?? [];
        $flags[$flag] = $value;
        $tenant->update(['feature_flags' => $flags]);
    }

    public function getConfiguration(string $tenantId, string $key, mixed $default = null): mixed
    {
        $tenant = $this->findById($tenantId);
        return data_get($tenant?->configurations, $key, $default);
    }

    public function setConfiguration(string $tenantId, string $key, mixed $value): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        $config = $tenant->configurations ?? [];
        data_set($config, $key, $value);
        $tenant->update(['configurations' => $config]);
    }

    public function getTokenLifetimes(string $tenantId): array
    {
        $tenant = $this->findById($tenantId);
        return $tenant?->token_lifetimes ?? [];
    }

    public function all(): Collection
    {
        return Tenant::active()->get();
    }
}
