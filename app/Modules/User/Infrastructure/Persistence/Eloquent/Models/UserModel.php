<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasAudit;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class UserModel extends Authenticatable
{
    use HasAudit, HasTenant, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'tenant_id',
        'org_unit_id',
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'locale',
        'timezone',
        'status',
        'preferences',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id' => 'int',
        'tenant_id' => 'int',
        'org_unit_id' => 'int',
        'preferences' => 'array',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
