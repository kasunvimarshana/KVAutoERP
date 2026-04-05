<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class StockAdjustmentModel extends BaseModel
{
    use HasTenant;

    protected $table = 'stock_adjustments';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'location_id',
        'reference_no',
        'adjustment_date',
        'reason',
        'notes',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'id'              => 'int',
        'tenant_id'       => 'int',
        'warehouse_id'    => 'int',
        'location_id'     => 'int',
        'created_by'      => 'int',
        'posted_by'       => 'int',
        'adjustment_date' => 'date',
        'posted_at'       => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];
}
