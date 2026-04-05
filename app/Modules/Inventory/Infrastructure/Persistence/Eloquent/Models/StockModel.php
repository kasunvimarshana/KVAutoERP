<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class StockModel extends BaseModel
{
    use HasTenant;

    protected $table = 'stocks';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'location_id',
        'quantity',
        'reserved_quantity',
        'unit',
        'last_movement_at',
    ];

    protected $casts = [
        'quantity'          => 'float',
        'reserved_quantity' => 'float',
        'last_movement_at'  => 'datetime',
    ];
}
