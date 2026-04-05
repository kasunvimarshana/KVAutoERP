<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class InventoryAdjustmentLineModel extends BaseModel
{
    use HasTenant;

    protected $table = 'inventory_adjustment_lines';

    protected $fillable = [
        'tenant_id',
        'adjustment_id',
        'product_id',
        'variant_id',
        'expected_quantity',
        'actual_quantity',
        'unit_cost',
        'batch_lot_id',
    ];

    protected $casts = [
        'expected_quantity' => 'float',
        'actual_quantity'   => 'float',
        'unit_cost'         => 'float',
    ];
}
