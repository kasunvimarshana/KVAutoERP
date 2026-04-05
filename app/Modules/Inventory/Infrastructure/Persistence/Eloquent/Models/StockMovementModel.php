<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasAudit;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

/**
 * StockMovements are immutable audit records — no soft deletes.
 */
class StockMovementModel extends Model
{
    use HasAudit, HasTenant;

    protected $table = 'stock_movements';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'from_location_id',
        'to_location_id',
        'quantity',
        'type',
        'reference',
        'batch_number',
        'lot_number',
        'serial_number',
        'expiry_date',
        'cost',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity'    => 'float',
        'cost'        => 'float',
        'expiry_date' => 'date',
    ];
}
