<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class StockItemModel extends BaseModel
{
    use HasTenant;

    protected $table = 'stock_items';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'warehouse_id',
        'location_id',
        'quantity',
        'reserved_quantity',
        'unit_cost',
        'last_movement_at',
    ];

    protected $casts = [
        'id'                => 'int',
        'tenant_id'         => 'int',
        'product_id'        => 'int',
        'variant_id'        => 'int',
        'warehouse_id'      => 'int',
        'location_id'       => 'int',
        'quantity'          => 'float',
        'reserved_quantity' => 'float',
        'unit_cost'         => 'float',
        'last_movement_at'  => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];
}
