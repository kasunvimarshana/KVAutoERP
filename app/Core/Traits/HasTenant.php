<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * HasTenant
 *
 * Automatically scopes Eloquent queries to the authenticated tenant.
 * Include this trait on any model that is tenant-owned.
 */
trait HasTenant
{
    /**
     * Boot the HasTenant trait.
     *
     * Registers a global scope that constrains all queries to the current
     * tenant, and automatically sets `tenant_id` on model creation.
     */
    public static function bootHasTenant(): void
    {
        static::addGlobalScope('tenant', function ($builder): void {
            $tenantId = static::resolveTenantId();

            if ($tenantId !== null) {
                $builder->where(
                    (new static())->getTable() . '.tenant_id',
                    $tenantId
                );
            }
        });

        static::creating(function (self $model): void {
            if (empty($model->tenant_id)) {
                $model->tenant_id = static::resolveTenantId();
            }
        });
    }

    /**
     * Resolve the current tenant's ID from the authenticated user or
     * the request header.
     *
     * @return int|string|null
     */
    protected static function resolveTenantId(): int|string|null
    {
        // 1. Prefer tenant stored on the authenticated user
        $user = Auth::user();

        if ($user && isset($user->tenant_id)) {
            return $user->tenant_id;
        }

        // 2. Fall back to X-Tenant-ID request header (service-to-service calls)
        $tenantId = request()->header('X-Tenant-ID');

        return $tenantId ?: null;
    }

    /**
     * Disable the tenant scope for a query (super-admin use-case).
     *
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public static function withoutTenantScope(): \Illuminate\Database\Eloquent\Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
