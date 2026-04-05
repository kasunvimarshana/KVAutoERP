<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class ProductVariantModel extends BaseModel
{
    use HasTenant;

    protected $table = 'product_variants';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'name',
        'sku',
        'barcode',
        'attributes',
        'cost_price',
        'selling_price',
        'stock_qty',
        'is_active',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'product_id'   => 'int',
        'attributes'   => 'array',
        'cost_price'   => 'float',
        'selling_price' => 'float',
        'stock_qty'    => 'float',
        'is_active'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}
