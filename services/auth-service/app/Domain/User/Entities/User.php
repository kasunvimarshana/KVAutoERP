<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * User Entity.
 *
 * Tenant-aware authenticatable user supporting multi-guard SSO,
 * RBAC/ABAC authorization, and per-device token management.
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'status',
        'metadata',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
        'metadata'          => 'array',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Domain\Tenant\Entities\Tenant::class, 'tenant_id');
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\Auth\Entities\Role::class,
            'user_roles',
            'user_id',
            'role_id',
        )->withTimestamps();
    }

    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\Auth\Entities\Permission::class,
            'user_permissions',
            'user_id',
            'permission_id',
        )->withTimestamps();
    }

    // =========================================================================
    // Authorization (RBAC + ABAC)
    // =========================================================================

    /**
     * Check if the user has a specific role (RBAC).
     *
     * @param  string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if the user has a specific permission via role or direct assignment (RBAC).
     *
     * @param  string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Direct permission assignment
        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        // Role-based permission
        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('name', $permission))
            ->exists();
    }

    /**
     * Check if the user can perform an action on a resource (ABAC).
     *
     * Evaluates attribute-based conditions (tenant context, resource ownership, etc.).
     *
     * @param  string               $action
     * @param  string               $resource
     * @param  array<string, mixed> $attributes Additional context attributes
     * @return bool
     */
    public function canPerform(string $action, string $resource, array $attributes = []): bool
    {
        // Check RBAC first
        $permission = "{$action}:{$resource}";
        if ($this->hasPermission($permission)) {
            return true;
        }

        // ABAC: Evaluate attribute policies
        return app(\App\Domain\Auth\Services\AbacPolicyService::class)
            ->evaluate($this, $action, $resource, $attributes);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }
}
