<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class OrderLineModel extends BaseModel
{
    protected $table = 'order_lines';

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'description',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'tax_group_id',
        'total_amount',
        'warehouse_id',
        'location_id',
        'batch_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'id'              => 'int',
        'order_id'        => 'int',
        'product_id'      => 'int',
        'variant_id'      => 'int',
        'tax_group_id'    => 'int',
        'warehouse_id'    => 'int',
        'location_id'     => 'int',
        'batch_id'        => 'int',
        'quantity'        => 'float',
        'unit_price'      => 'float',
        'discount_amount' => 'float',
        'tax_amount'      => 'float',
        'total_amount'    => 'float',
        'metadata'        => 'array',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];
}
