<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'organization_id',
        'branch_id',
        'location_id',
        'department_id',
        'status',
        'token_version',
        'iam_provider',
        'external_id',
        'phone',
        'avatar',
        'metadata',
        'last_login_at',
        'last_login_ip',
        'last_login_device',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'metadata'          => 'array',
            'last_login_at'     => 'datetime',
            'token_version'     => 'integer',
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot(['tenant_id', 'assigned_by', 'created_at']);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function iamMappings(): HasMany
    {
        return $this->hasMany(UserIamMapping::class);
    }

    // ──────────────────────────────────────────────────────────
    // Helper methods
    // ──────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getRoleNames(): array
    {
        return $this->roles->pluck('slug')->all();
    }

    public function getAllPermissions(): Collection
    {
        return $this->roles->flatMap(
            fn (Role $role) => $role->permissions
        )->unique('id');
    }

    public function getPermissionNames(): array
    {
        return $this->getAllPermissions()->pluck('slug')->all();
    }
}
