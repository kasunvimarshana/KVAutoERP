<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class PriceListItemModel extends BaseModel
{
    use HasTenant;

    protected $table = 'price_list_items';

    protected $fillable = [
        'tenant_id',
        'price_list_id',
        'product_id',
        'variant_id',
        'price_type',
        'price',
        'min_quantity',
        'max_quantity',
        'notes',
    ];

    protected $casts = [
        'price'        => 'float',
        'min_quantity' => 'float',
        'max_quantity' => 'float',
        'product_id'   => 'int',
        'variant_id'   => 'int',
        'price_list_id'=> 'int',
    ];
}
