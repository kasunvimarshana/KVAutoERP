<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class InventoryAdjustmentModel extends BaseModel
{
    use HasTenant;

    protected $table = 'inventory_adjustments';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'status',
        'reason',
        'adjusted_by',
        'approved_by',
        'applied_at',
        'notes',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'warehouse_id' => 'int',
        'adjusted_by'  => 'int',
        'approved_by'  => 'int',
        'applied_at'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}
