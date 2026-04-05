<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasAudit;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

/**
 * ValuationLayers are immutable cost records — no soft deletes.
 */
class ValuationLayerModel extends Model
{
    use HasAudit, HasTenant;

    protected $table = 'valuation_layers';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'location_id',
        'batch_lot_id',
        'quantity',
        'remaining_quantity',
        'unit_cost',
        'valuation_method',
        'received_at',
        'reference',
    ];

    protected $casts = [
        'quantity'           => 'float',
        'remaining_quantity' => 'float',
        'unit_cost'          => 'float',
        'received_at'        => 'datetime',
    ];
}
