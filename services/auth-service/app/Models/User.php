<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'id',
        'tenant_id',
        'organisation_id',
        'branch_id',
        'location_id',
        'department_id',
        'name',
        'email',
        'password',
        'token_version',
        'is_active',
        'is_locked',
        'locked_until',
        'failed_login_attempts',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
        'password_changed_at',
        'metadata',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'last_login_at'        => 'datetime',
        'locked_until'         => 'datetime',
        'password_changed_at'  => 'datetime',
        'is_active'            => 'boolean',
        'is_locked'            => 'boolean',
        'token_version'        => 'integer',
        'failed_login_attempts' => 'integer',
        'metadata'             => 'array',
        'password'             => 'hashed',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function organisation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function deviceSessions(): HasMany
    {
        return $this->hasMany(DeviceSession::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['organisation_id', 'branch_id'])
            ->withTimestamps();
    }

    public function directPermissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot(['granted', 'organisation_id'])
            ->withTimestamps();
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('locked_until')->orWhere('locked_until', '<', now());
        });
    }

    // -----------------------------------------------------------------
    // Helper Methods
    // -----------------------------------------------------------------

    public function isLocked(): bool
    {
        return $this->is_locked && ($this->locked_until === null || $this->locked_until->isFuture());
    }

    public function incrementTokenVersion(): void
    {
        $this->increment('token_version');
    }

    public function getAllPermissions(): array
    {
        $rolePermissions = $this->roles->flatMap(fn ($role) => $role->permissions->pluck('name'));
        $directPermissions = $this->directPermissions->where('pivot.granted', true)->pluck('name');

        return $rolePermissions->merge($directPermissions)->unique()->values()->toArray();
    }

    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }
}
