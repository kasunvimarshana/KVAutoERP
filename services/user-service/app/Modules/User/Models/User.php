<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'keycloak_id',
        'username',
        'email',
        'first_name',
        'last_name',
        'phone',
        'roles',
        'attributes',
        'is_active',
        'email_verified_at',
    ];

    protected $casts = [
        'roles'             => 'array',
        'attributes'        => 'array',
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'remember_token',
    ];

    const ROLE_ADMIN             = 'admin';
    const ROLE_MANAGER           = 'manager';
    const ROLE_WAREHOUSE_MANAGER = 'warehouse-manager';
    const ROLE_VIEWER            = 'viewer';
    const ROLE_CUSTOMER          = 'customer';

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Check if user has a specific role (RBAC).
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? []);
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return !empty(array_intersect($roles, $this->roles ?? []));
    }

    /**
     * Check ABAC attribute.
     */
    public function hasAttribute(string $key, mixed $value): bool
    {
        $attributes = $this->attributes ?? [];
        return isset($attributes[$key]) && $attributes[$key] === $value;
    }

    /**
     * Scope for active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for searching users.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('username', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope for filtering by role.
     */
    public function scopeWithRole(Builder $query, string $role): Builder
    {
        return $query->whereJsonContains('roles', $role);
    }
}
