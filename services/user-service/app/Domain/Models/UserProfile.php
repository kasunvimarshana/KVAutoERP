<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * UserProfile Domain Model
 *
 * Stores extended profile data for users managed by the Auth Service.
 * user_id is a logical reference (not a FK) to the auth-service users table.
 */
class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'avatar',
        'bio',
        'phone',
        'address',
        'preferences',
        'notification_settings',
        'timezone',
        'locale',
        'theme',
        'extra_permissions',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'address' => 'array',
        'preferences' => 'array',
        'notification_settings' => 'array',
        'extra_permissions' => 'array',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_profile_id',
            'role_id'
        );
    }

    public function scopeForTenant($query, string|int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
