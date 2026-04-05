<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class InventoryAdjustmentModel extends BaseModel
{
    use HasTenant;

    protected $table = 'inventory_adjustments';

    protected $fillable = [
        'tenant_id',
        'adjustment_number',
        'date',
        'location_id',
        'status',
        'reason',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
