<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class WarehouseModel extends BaseModel
{
    use HasTenant;

    protected $table = 'warehouses';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'address',
        'is_default',
        'is_active',
        'manager_id',
        'notes',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'manager_id' => 'int',
        'address'    => 'array',
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
