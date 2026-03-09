<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Permission Entity (RBAC/ABAC).
 */
class Permission extends Model
{
    use HasUuids;

    protected $table = 'permissions';

    protected $fillable = [
        'name',          // e.g., 'inventory:read', 'order:create'
        'display_name',
        'description',
        'guard_name',
        'resource',      // e.g., 'inventory', 'order'
        'action',        // e.g., 'read', 'create', 'update', 'delete'
    ];

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'permission_id',
            'role_id',
        )->withTimestamps();
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\User\Entities\User::class,
            'user_permissions',
            'permission_id',
            'user_id',
        )->withTimestamps();
    }
}
