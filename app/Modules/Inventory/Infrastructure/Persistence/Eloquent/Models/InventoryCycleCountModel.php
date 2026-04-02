<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class InventoryCycleCountModel extends BaseModel
{
    protected $table = 'inventory_cycle_counts';

    protected $fillable = [
        'tenant_id', 'reference_number', 'warehouse_id', 'zone_id', 'location_id',
        'count_method', 'status', 'assigned_to', 'scheduled_at',
        'started_at', 'completed_at', 'notes', 'metadata',
    ];

    protected $casts = [
        'tenant_id' => 'integer', 'warehouse_id' => 'integer', 'zone_id' => 'integer',
        'location_id' => 'integer', 'assigned_to' => 'integer',
        'scheduled_at' => 'datetime', 'started_at' => 'datetime',
        'completed_at' => 'datetime', 'metadata' => 'array',
    ];
}
