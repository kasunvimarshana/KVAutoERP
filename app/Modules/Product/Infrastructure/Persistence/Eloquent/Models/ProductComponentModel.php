<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ProductComponentModel extends Model
{
    protected $table = 'product_components';

    protected $fillable = [
        'product_id',
        'component_product_id',
        'component_variant_id',
        'quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'id'                   => 'int',
        'product_id'           => 'int',
        'component_product_id' => 'int',
        'component_variant_id' => 'int',
        'quantity'             => 'float',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];
}
