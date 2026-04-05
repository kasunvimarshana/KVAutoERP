<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class CycleCountModel extends Model
{
    use HasTenant;

    protected $table = 'cycle_counts';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'status',
        'started_at',
        'completed_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'warehouse_id' => 'int',
        'created_by'   => 'int',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];
}
