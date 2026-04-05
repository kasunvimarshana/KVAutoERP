<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class StockMovementModel extends Model
{
    use HasTenant;

    protected $table = 'stock_movements';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'warehouse_id',
        'location_id',
        'type',
        'reference_type',
        'reference_id',
        'quantity',
        'direction',
        'unit_cost',
        'batch_id',
        'lot_number',
        'serial_number',
        'notes',
        'performed_by',
        'performed_at',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'product_id'   => 'int',
        'variant_id'   => 'int',
        'warehouse_id' => 'int',
        'location_id'  => 'int',
        'reference_id' => 'int',
        'batch_id'     => 'int',
        'performed_by' => 'int',
        'quantity'     => 'float',
        'unit_cost'    => 'float',
        'performed_at' => 'datetime',
        'metadata'     => 'array',
        'created_at'   => 'datetime',
    ];
}
