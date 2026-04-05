<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class ProductModel extends BaseModel
{
    use HasTenant;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'barcode',
        'type',
        'category_id',
        'description',
        'unit',
        'cost_price',
        'selling_price',
        'tax_group_id',
        'track_inventory',
        'is_active',
        'weight',
        'dimensions',
        'images',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'id'              => 'int',
        'tenant_id'       => 'int',
        'category_id'     => 'int',
        'tax_group_id'    => 'int',
        'cost_price'      => 'float',
        'selling_price'   => 'float',
        'weight'          => 'float',
        'track_inventory' => 'boolean',
        'is_active'       => 'boolean',
        'dimensions'      => 'array',
        'images'          => 'array',
        'tags'            => 'array',
        'metadata'        => 'array',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];
}
