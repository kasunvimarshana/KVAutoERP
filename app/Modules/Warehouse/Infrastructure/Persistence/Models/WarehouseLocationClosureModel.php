<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseLocationClosureModel extends Model
{
    protected $table = 'warehouse_location_closures';

    public $timestamps = false;

    protected $fillable = [
        'ancestor_id',
        'descendant_id',
        'depth',
    ];

    protected $casts = [
        'ancestor_id'   => 'int',
        'descendant_id' => 'int',
        'depth'         => 'int',
    ];
}
