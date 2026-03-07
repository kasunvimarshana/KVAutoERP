<?php

namespace App\Core\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantManager
{
    protected ?Tenant $currentTenant = null;

    public function setTenant(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
        app()->instance('current_tenant', $tenant);
    }

    public function getTenant(): ?Tenant
    {
        return $this->currentTenant;
    }

    public function getTenantId(): ?int
    {
        return $this->currentTenant?->id;
    }

    public function getTenantKey(): ?string
    {
        return $this->currentTenant?->key;
    }

    public function hasTenant(): bool
    {
        return $this->currentTenant !== null;
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        if (!$this->currentTenant) {
            return $default;
        }

        $config = Cache::remember(
            "tenant_{$this->currentTenant->id}_config",
            3600,
            fn () => $this->currentTenant->configurations()->pluck('value', 'key')->toArray()
        );

        return $config[$key] ?? $default;
    }

    public function clearCache(): void
    {
        if ($this->currentTenant) {
            Cache::forget("tenant_{$this->currentTenant->id}_config");
        }
    }
}
