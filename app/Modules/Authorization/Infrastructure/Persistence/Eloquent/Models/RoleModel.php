<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class RoleModel extends BaseModel
{
    protected $table = 'roles';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'id' => 'int',
        'tenant_id' => 'int',
        'is_system' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
