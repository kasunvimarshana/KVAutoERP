<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class ValuationLayerModel extends Model
{
    use HasTenant;

    protected $table = 'valuation_layers';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'warehouse_id',
        'location_id',
        'batch_id',
        'quantity',
        'original_quantity',
        'unit_cost',
        'method',
        'created_at',
    ];

    protected $casts = [
        'id'                => 'int',
        'tenant_id'         => 'int',
        'product_id'        => 'int',
        'variant_id'        => 'int',
        'warehouse_id'      => 'int',
        'location_id'       => 'int',
        'batch_id'          => 'int',
        'quantity'          => 'float',
        'original_quantity' => 'float',
        'unit_cost'         => 'float',
        'created_at'        => 'datetime',
    ];
}
