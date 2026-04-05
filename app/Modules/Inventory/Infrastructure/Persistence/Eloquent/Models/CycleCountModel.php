<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class CycleCountModel extends BaseModel
{
    use HasTenant;

    protected $table = 'cycle_counts';

    protected $fillable = [
        'tenant_id',
        'count_number',
        'location_id',
        'status',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];
}
