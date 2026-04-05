<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BatchModel extends BaseModel
{
    use HasTenant;

    protected $table = 'batches';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'batch_number',
        'lot_number',
        'serial_number',
        'expiry_date',
        'manufacture_date',
        'supplier_id',
        'quantity',
        'received_quantity',
        'status',
        'warehouse_id',
        'location_id',
        'cost_per_unit',
        'metadata',
    ];

    protected $casts = [
        'id'               => 'int',
        'tenant_id'        => 'int',
        'product_id'       => 'int',
        'variant_id'       => 'int',
        'supplier_id'      => 'int',
        'warehouse_id'     => 'int',
        'location_id'      => 'int',
        'quantity'         => 'float',
        'received_quantity' => 'float',
        'cost_per_unit'    => 'float',
        'expiry_date'      => 'date',
        'manufacture_date' => 'date',
        'metadata'         => 'array',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];
}
