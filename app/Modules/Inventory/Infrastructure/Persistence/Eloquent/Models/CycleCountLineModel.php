<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

final class CycleCountLineModel extends BaseModel
{
    protected $table = 'cycle_count_lines';

    protected $fillable = [
        'cycle_count_id',
        'product_id',
        'product_variant_id',
        'system_qty',
        'counted_qty',
        'variance',
        'notes',
    ];

    protected $casts = [
        'id'                 => 'int',
        'cycle_count_id'     => 'int',
        'product_id'         => 'int',
        'product_variant_id' => 'int',
        'system_qty'         => 'float',
        'counted_qty'        => 'float',
        'variance'           => 'float',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];
}
