<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Role Domain Model
 *
 * Represents a role in the RBAC system.
 * Roles are tenant-scoped and contain a set of permissions.
 */
class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    public function userProfiles()
    {
        return $this->belongsToMany(
            UserProfile::class,
            'user_roles',
            'role_id',
            'user_profile_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTenant($query, string|int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
