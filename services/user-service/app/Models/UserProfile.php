<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * UserProfile domain entity.
 *
 * Stores the extended profile for an identity managed by the Auth Service.
 * Cross-service reference: `auth_user_id` references the user in auth-service
 * (no foreign key constraint enforced since it is a cross-service relationship).
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string|null $organization_id
 * @property string|null $branch_id
 * @property string|null $location_id
 * @property string|null $department_id
 * @property string      $auth_user_id
 * @property string      $email
 * @property string      $first_name
 * @property string      $last_name
 * @property string|null $display_name
 * @property string|null $avatar_url
 * @property string|null $phone
 * @property string|null $locale
 * @property string|null $timezone
 * @property array|null  $metadata
 * @property bool        $is_active
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class UserProfile extends TenantAwareModel
{
    /** @var string */
    protected $table = 'user_profiles';

    /** @var array<string, string> */
    protected $casts = [
        'metadata'   => 'array',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array<int, string> */
    protected $hidden = ['metadata'];

    /**
     * Roles belonging to this user profile (via user_roles pivot).
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_profile_id',
            'role_id',
        )->withPivot(['tenant_id', 'granted_by', 'granted_at', 'expires_at'])
         ->withTimestamps();
    }

    /**
     * Direct permissions assigned to this user profile (via user_permissions pivot).
     *
     * @return BelongsToMany<Permission>
     */
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_permissions',
            'user_profile_id',
            'permission_id',
        )->withPivot(['tenant_id', 'is_granted', 'granted_by'])
         ->withTimestamps();
    }

    /**
     * Permissions inherited from all assigned roles (via role_permissions pivot).
     *
     * @return HasManyThrough<Permission>
     */
    public function permissions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Permission::class,
            Role::class,
            'id',       // FK on roles
            'id',       // FK on permissions
            'id',       // local key on user_profiles
            'id',       // local key on roles
        );
    }

    /**
     * Compute the user's full display name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Determine whether the user holds the given role slug.
     *
     * @param  string  $roleSlug
     * @return bool
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug', $roleSlug);
    }

    /**
     * Determine whether the user holds the given permission slug
     * (via any assigned role).
     *
     * @param  string  $permissionSlug
     * @return bool
     */
    public function hasPermission(string $permissionSlug): bool
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }
}
