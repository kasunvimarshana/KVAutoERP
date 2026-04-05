<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

final class StockAdjustmentLineModel extends BaseModel
{
    protected $table = 'stock_adjustment_lines';

    protected $fillable = [
        'adjustment_id',
        'product_id',
        'product_variant_id',
        'expected_qty',
        'actual_qty',
        'variance',
        'cost_per_unit',
        'batch_number',
        'lot_number',
        'serial_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'id'                 => 'int',
        'adjustment_id'      => 'int',
        'product_id'         => 'int',
        'product_variant_id' => 'int',
        'expected_qty'       => 'float',
        'actual_qty'         => 'float',
        'variance'           => 'float',
        'cost_per_unit'      => 'float',
        'expiry_date'        => 'date',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];
}
