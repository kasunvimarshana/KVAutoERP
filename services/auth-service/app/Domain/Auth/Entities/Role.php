<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Role Entity (RBAC).
 */
class Role extends Model
{
    use HasUuids;

    protected $table = 'roles';

    protected $fillable = [
        'tenant_id',
        'name',
        'display_name',
        'description',
        'guard_name',
    ];

    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id',
        )->withTimestamps();
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\User\Entities\User::class,
            'user_roles',
            'role_id',
            'user_id',
        )->withTimestamps();
    }

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
}
