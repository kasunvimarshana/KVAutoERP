<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class StockItemModel extends BaseModel
{
    use HasTenant;

    protected $table = 'stock_items';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'location_id',
        'quantity_available',
        'quantity_reserved',
        'quantity_on_order',
        'unit_of_measure',
    ];

    protected $casts = [
        'id'                 => 'int',
        'tenant_id'          => 'int',
        'product_id'         => 'int',
        'product_variant_id' => 'int',
        'warehouse_id'       => 'int',
        'location_id'        => 'int',
        'quantity_available' => 'float',
        'quantity_reserved'  => 'float',
        'quantity_on_order'  => 'float',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];
}
