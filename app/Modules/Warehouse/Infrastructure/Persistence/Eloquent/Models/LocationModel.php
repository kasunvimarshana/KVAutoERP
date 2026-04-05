<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class LocationModel extends BaseModel
{
    use HasTenant;

    protected $table = 'locations';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'name',
        'code',
        'type',
        'parent_id',
        'path',
        'level',
        'capacity',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'warehouse_id' => 'int',
        'parent_id'    => 'int',
        'level'        => 'int',
        'capacity'     => 'float',
        'is_active'    => 'boolean',
        'metadata'     => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}
