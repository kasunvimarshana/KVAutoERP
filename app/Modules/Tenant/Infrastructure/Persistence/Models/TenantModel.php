<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class TenantModel extends BaseModel
{
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'plan',
        'settings',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id'         => 'int',
        'settings'   => 'array',
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
