<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class CycleCountLineModel extends Model
{
    protected $table = 'cycle_count_lines';

    protected $fillable = [
        'cycle_count_id',
        'product_id',
        'variant_id',
        'location_id',
        'batch_id',
        'expected_quantity',
        'counted_quantity',
        'variance',
        'status',
        'notes',
    ];

    protected $casts = [
        'id'                => 'int',
        'cycle_count_id'    => 'int',
        'product_id'        => 'int',
        'variant_id'        => 'int',
        'location_id'       => 'int',
        'batch_id'          => 'int',
        'expected_quantity' => 'float',
        'counted_quantity'  => 'float',
        'variance'          => 'float',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];
}
