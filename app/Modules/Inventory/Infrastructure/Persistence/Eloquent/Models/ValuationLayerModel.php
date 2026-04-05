<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class ValuationLayerModel extends BaseModel
{
    use HasTenant;

    protected $table = 'valuation_layers';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'batch_number',
        'lot_number',
        'serial_number',
        'received_at',
        'expiry_date',
        'quantity_received',
        'quantity_remaining',
        'cost_per_unit',
        'valuation_method',
        'is_exhausted',
    ];

    protected $casts = [
        'id'                  => 'int',
        'tenant_id'           => 'int',
        'product_id'          => 'int',
        'product_variant_id'  => 'int',
        'warehouse_id'        => 'int',
        'quantity_received'   => 'float',
        'quantity_remaining'  => 'float',
        'cost_per_unit'       => 'float',
        'received_at'         => 'date',
        'expiry_date'         => 'date',
        'is_exhausted'        => 'bool',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];
}
