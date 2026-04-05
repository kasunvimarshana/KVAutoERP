<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class CycleCountModel extends BaseModel
{
    use HasTenant;

    protected $table = 'cycle_counts';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'location_id',
        'reference_no',
        'status',
        'scheduled_date',
        'completed_date',
        'created_by',
    ];

    protected $casts = [
        'id'             => 'int',
        'tenant_id'      => 'int',
        'warehouse_id'   => 'int',
        'location_id'    => 'int',
        'created_by'     => 'int',
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}
