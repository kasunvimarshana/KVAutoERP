<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role_id',
        'tenant_id',
    ];

    protected $casts = [
        'user_id'   => 'int',
        'role_id'   => 'int',
        'tenant_id' => 'int',
    ];
}
