<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class StockReservationModel extends BaseModel
{
    use HasTenant;

    protected $table = 'stock_reservations';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'location_id',
        'quantity',
        'reference_type',
        'reference_id',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'expires_at' => 'datetime',
    ];
}
