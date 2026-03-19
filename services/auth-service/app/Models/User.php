<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * User model — the primary identity entity in the auth service.
 *
 * Extends TenantAwareModel so all queries are automatically scoped
 * to the current tenant. Uses Argon2id for password hashing (enforced
 * at the service layer via auth_service.password_algo config).
 *
 * @property string                    $id
 * @property string                    $tenant_id
 * @property string|null               $organization_id
 * @property string|null               $branch_id
 * @property string                    $email
 * @property string                    $password
 * @property string                    $first_name
 * @property string                    $last_name
 * @property array<int, string>        $roles
 * @property array<int, string>        $permissions
 * @property array<string, mixed>      $device_sessions
 * @property int                       $token_version
 * @property bool                      $is_active
 * @property string|null               $created_by
 * @property string|null               $updated_by
 * @property \Carbon\Carbon            $created_at
 * @property \Carbon\Carbon            $updated_at
 */
class User extends TenantAwareModel implements AuthenticatableContract
{
    use Authenticatable;

    /** @var string */
    protected $table = 'users';

    /**
     * Attributes excluded from mass assignment.
     *
     * @var array<int, string>
     */
    protected $guarded = ['token_version'];

    /**
     * Attributes hidden from serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'device_sessions',
    ];

    /**
     * Attribute casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'roles'           => 'array',
        'permissions'     => 'array',
        'device_sessions' => 'array',
        'is_active'       => 'boolean',
        'token_version'   => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Return the user's full display name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Determine whether the user has the given role slug.
     *
     * @param  string  $role  Role slug.
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? [], true);
    }

    /**
     * Determine whether the user has the given permission slug.
     *
     * @param  string  $permission  Permission slug.
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * Determine whether the user account is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Return the refresh tokens relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refreshTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RefreshToken::class, 'user_id');
    }

    /**
     * Return the audit logs relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuthAuditLog::class, 'user_id');
    }
}
