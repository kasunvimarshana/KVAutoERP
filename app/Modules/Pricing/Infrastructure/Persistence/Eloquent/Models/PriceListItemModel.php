<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class PriceListItemModel extends BaseModel
{
    protected $table = 'price_list_items';

    protected $fillable = [
        'price_list_id',
        'product_id',
        'variant_id',
        'price_type',
        'price',
        'min_quantity',
        'max_quantity',
        'is_active',
    ];

    protected $casts = [
        'id'            => 'int',
        'price_list_id' => 'int',
        'product_id'    => 'int',
        'variant_id'    => 'int',
        'price'         => 'float',
        'min_quantity'  => 'float',
        'max_quantity'  => 'float',
        'is_active'     => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];
}
