<?php

declare(strict_types=1);

namespace App\Modules\Auth\Domain\Models;

use App\Core\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User model.
 *
 * Tenant-aware user with Passport SSO, RBAC via Spatie,
 * and ABAC-ready attribute storage.
 *
 * @property int         $id
 * @property int         $tenant_id
 * @property string      $name
 * @property string      $email
 * @property string      $password
 * @property string|null $avatar
 * @property array|null  $abac_attributes  ABAC attribute bag (role, department, clearance, etc.)
 * @property bool        $is_active
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use HasTenant;
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'avatar',
        'abac_attributes',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'abac_attributes'   => 'array',
        'is_active'         => 'boolean',
    ];

    // -------------------------------------------------------------------------
    //  ABAC helper
    // -------------------------------------------------------------------------

    /**
     * Retrieve a specific ABAC attribute from the stored attributes bag.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getAbacAttribute(string $key, mixed $default = null): mixed
    {
        return data_get($this->abac_attributes ?? [], $key, $default);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
