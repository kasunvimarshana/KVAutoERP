<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BatchLotModel extends BaseModel
{
    use HasTenant;

    protected $table = 'batch_lots';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'batch_number',
        'lot_number',
        'serial_number',
        'expiry_date',
        'manufacturing_date',
        'quantity',
        'remaining_quantity',
        'location_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'quantity'           => 'float',
        'remaining_quantity' => 'float',
        'metadata'           => 'array',
        'expiry_date'        => 'date',
        'manufacturing_date' => 'date',
    ];
}
