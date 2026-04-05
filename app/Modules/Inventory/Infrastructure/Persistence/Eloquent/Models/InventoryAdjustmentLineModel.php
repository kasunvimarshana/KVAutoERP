<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentLineModel extends Model
{
    protected $table = 'inventory_adjustment_lines';

    protected $fillable = [
        'adjustment_id',
        'product_id',
        'variant_id',
        'location_id',
        'expected_quantity',
        'actual_quantity',
        'variance',
        'batch_id',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'id'                => 'int',
        'adjustment_id'     => 'int',
        'product_id'        => 'int',
        'variant_id'        => 'int',
        'location_id'       => 'int',
        'batch_id'          => 'int',
        'expected_quantity' => 'float',
        'actual_quantity'   => 'float',
        'variance'          => 'float',
        'unit_cost'         => 'float',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];
}
