<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class StockMovementModel extends BaseModel
{
    use HasTenant;

    protected $table = 'stock_movements';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'product_variant_id',
        'from_location_id',
        'to_location_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
        'batch_number',
        'lot_number',
        'serial_number',
        'expiry_date',
        'cost_per_unit',
        'notes',
        'moved_by',
        'moved_at',
    ];

    protected $casts = [
        'id'                 => 'int',
        'tenant_id'          => 'int',
        'product_id'         => 'int',
        'product_variant_id' => 'int',
        'from_location_id'   => 'int',
        'to_location_id'     => 'int',
        'quantity'           => 'float',
        'cost_per_unit'      => 'float',
        'expiry_date'        => 'date',
        'moved_at'           => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];
}
