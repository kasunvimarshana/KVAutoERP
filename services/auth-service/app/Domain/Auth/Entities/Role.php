<?php

namespace App\Domain\Auth\Entities;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Extends Spatie's Role to carry tenant scoping.
 *
 * Tenant-scoped roles allow the same role name to exist per tenant
 * without collisions (e.g. "manager" for tenant-A ≠ "manager" for tenant-B).
 */
class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'tenant_id',
        'description',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId)
                     ->orWhereNull('tenant_id');  // global roles visible to all tenants
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isGlobal(): bool
    {
        return is_null($this->tenant_id);
    }

    public function isTenantSpecific(): bool
    {
        return !is_null($this->tenant_id);
    }
}
