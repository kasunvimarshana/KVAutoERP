<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class StockReservationModel extends BaseModel
{
    use HasTenant;

    protected $table = 'stock_reservations';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'product_variant_id',
        'location_id',
        'quantity',
        'reference_type',
        'reference_id',
        'expires_at',
    ];

    protected $casts = [
        'id'                 => 'int',
        'tenant_id'          => 'int',
        'product_id'         => 'int',
        'product_variant_id' => 'int',
        'location_id'        => 'int',
        'quantity'           => 'float',
        'expires_at'         => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];
}
