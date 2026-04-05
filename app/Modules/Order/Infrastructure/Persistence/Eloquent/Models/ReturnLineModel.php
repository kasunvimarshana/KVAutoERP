<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class ReturnLineModel extends BaseModel
{
    protected $table = 'return_lines';

    protected $fillable = [
        'return_id',
        'order_line_id',
        'product_id',
        'variant_id',
        'batch_id',
        'quantity',
        'condition',
        'unit_price',
        'restock_to_warehouse_id',
        'restock_to_location_id',
        'should_restock',
        'notes',
    ];

    protected $casts = [
        'id'                      => 'int',
        'return_id'               => 'int',
        'order_line_id'           => 'int',
        'product_id'              => 'int',
        'variant_id'              => 'int',
        'batch_id'                => 'int',
        'restock_to_warehouse_id' => 'int',
        'restock_to_location_id'  => 'int',
        'quantity'                => 'float',
        'unit_price'              => 'float',
        'should_restock'          => 'boolean',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];
}
