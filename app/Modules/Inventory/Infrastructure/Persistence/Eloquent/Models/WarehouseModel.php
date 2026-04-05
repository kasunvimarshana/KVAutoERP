<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class WarehouseModel extends BaseModel
{
    use HasTenant;

    protected $table = 'warehouses';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'address',
        'is_active',
        'is_default',
        'manager_id',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'address'    => 'array',
        'is_active'  => 'bool',
        'is_default' => 'bool',
        'manager_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
