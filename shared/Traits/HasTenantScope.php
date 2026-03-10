<?php

declare(strict_types=1);

namespace Shared\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

/**
 * HasTenantScope Trait
 * 
 * Automatically scopes all queries to the current tenant.
 * Apply to Eloquent models that require tenant isolation.
 * 
 * Usage: Add `use HasTenantScope;` to your model.
 */
trait HasTenantScope
{
    /**
     * Boot the trait - adds global scope for tenant isolation.
     */
    public static function bootHasTenantScope(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenantId = static::resolveTenantId()) {
                $builder->where(static::getTenantColumn(), $tenantId);
            }
        });

        static::creating(function ($model) {
            if (!$model->{static::getTenantColumn()} && $tenantId = static::resolveTenantId()) {
                $model->{static::getTenantColumn()} = $tenantId;
            }
        });
    }

    /**
     * Resolve the current tenant ID from the request/context.
     * 
     * @return string|int|null
     */
    protected static function resolveTenantId(): string|int|null
    {
        // Try from DI container (set by TenantMiddleware)
        if (App::bound('current.tenant.id')) {
            return App::make('current.tenant.id');
        }

        // Try from config
        return config('tenant.id');
    }

    /**
     * Get the tenant column name. Override in model if different.
     * 
     * @return string
     */
    public static function getTenantColumn(): string
    {
        return defined(static::class . '::TENANT_COLUMN') ? static::TENANT_COLUMN : 'tenant_id';
    }

    /**
     * Scope a query to exclude the tenant scope (for admin/cross-tenant queries).
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope a query to a specific tenant.
     * 
     * @param Builder $query
     * @param string|int $tenantId
     * @return Builder
     */
    public function scopeForTenant(Builder $query, string|int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')->where(static::getTenantColumn(), $tenantId);
    }
}
