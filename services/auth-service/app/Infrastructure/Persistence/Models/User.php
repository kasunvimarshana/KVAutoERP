<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Eloquent Model.
 *
 * Infrastructure concern only — maps to the `users` table and provides the
 * Passport / Spatie Permission integration surface.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $name
 * @property string      $email
 * @property string|null $email_verified_at
 * @property string      $password
 * @property bool        $is_active
 * @property string|null $last_login_at
 * @property string|null $remember_token
 * @property string|null $deleted_at
 * @property string      $created_at
 * @property string      $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    /** @var string */
    protected $table = 'users';

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'email',
        'password',
        'email_verified_at',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialisation.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Scope a query to users belonging to a specific tenant.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $tenantId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    /**
     * The tenant this user belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Infrastructure\Persistence\Models\Tenant::class, 'tenant_id');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Domain entity conversion
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Convert this Eloquent model to the corresponding domain entity.
     */
    public function toDomainEntity(): \App\Domain\Auth\Entities\User
    {
        return \App\Domain\Auth\Entities\User::fromArray([
            'id'            => $this->id,
            'tenant_id'     => $this->tenant_id,
            'email'         => $this->email,
            'name'          => $this->name,
            'roles'         => $this->getRoleNames()->toArray(),
            'permissions'   => $this->getAllPermissions()->pluck('name')->toArray(),
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
        ]);
    }
}
