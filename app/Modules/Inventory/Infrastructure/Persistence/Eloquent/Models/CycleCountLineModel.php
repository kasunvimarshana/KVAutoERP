<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class CycleCountLineModel extends BaseModel
{
    use HasTenant;

    protected $table = 'cycle_count_lines';

    protected $fillable = [
        'tenant_id',
        'cycle_count_id',
        'product_id',
        'variant_id',
        'system_quantity',
        'counted_quantity',
        'batch_lot_id',
    ];

    protected $casts = [
        'system_quantity'  => 'float',
        'counted_quantity' => 'float',
    ];
}
