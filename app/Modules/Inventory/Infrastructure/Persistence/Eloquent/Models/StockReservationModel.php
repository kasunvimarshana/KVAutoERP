<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class StockReservationModel extends Model
{
    use HasTenant;

    protected $table = 'stock_reservations';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'warehouse_id',
        'location_id',
        'quantity',
        'reference_type',
        'reference_id',
        'status',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'product_id'   => 'int',
        'variant_id'   => 'int',
        'warehouse_id' => 'int',
        'location_id'  => 'int',
        'reference_id' => 'int',
        'quantity'     => 'float',
        'expires_at'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];
}
