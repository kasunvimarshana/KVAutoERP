<?php

namespace App\Domain\Auth\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'metadata',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'metadata'          => 'array',
        'is_active'         => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // -------------------------------------------------------------------------
    // Domain helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function belongsToTenant(string $tenantId): bool
    {
        return $this->tenant_id === $tenantId;
    }

    /**
     * Check a Spatie permission scoped to the user's own tenant.
     */
    public function hasPermissionForTenant(string $permission, string $tenantId): bool
    {
        return $this->tenant_id === $tenantId && $this->hasPermissionTo($permission);
    }

    /**
     * Build extra claims for Passport JWT / token introspection payloads.
     */
    public function getCustomClaims(): array
    {
        return [
            'tenant_id'   => $this->tenant_id,
            'roles'       => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'is_active'   => $this->is_active,
        ];
    }

    /**
     * Called by Passport when building the token payload.
     */
    public function findForPassport(string $username): ?self
    {
        return $this->where('email', $username)->first();
    }
}
