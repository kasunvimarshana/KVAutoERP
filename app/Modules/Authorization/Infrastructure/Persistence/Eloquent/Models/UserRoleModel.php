<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
        'tenant_id',
    ];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'role_id' => 'int',
        'tenant_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
