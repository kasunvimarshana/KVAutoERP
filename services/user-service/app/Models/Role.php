<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Role domain entity.
 *
 * Represents an RBAC role scoped to a tenant. Roles aggregate permissions
 * and are assigned to user profiles through the user_roles pivot table.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $name
 * @property string      $slug
 * @property string|null $description
 * @property int         $hierarchy_level
 * @property bool        $is_system
 * @property array|null  $metadata
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class Role extends TenantAwareModel
{
    /** @var string */
    protected $table = 'roles';

    /** @var array<string, string> */
    protected $casts = [
        'hierarchy_level' => 'integer',
        'is_system'       => 'boolean',
        'metadata'        => 'array',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Permissions granted to this role (via role_permissions pivot).
     *
     * @return BelongsToMany<Permission>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id',
        )->withPivot(['tenant_id'])
         ->withTimestamps();
    }

    /**
     * User profiles assigned to this role (via user_roles pivot).
     *
     * @return BelongsToMany<UserProfile>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            UserProfile::class,
            'user_roles',
            'role_id',
            'user_profile_id',
        )->withPivot(['tenant_id', 'granted_by', 'granted_at', 'expires_at'])
         ->withTimestamps();
    }
}
