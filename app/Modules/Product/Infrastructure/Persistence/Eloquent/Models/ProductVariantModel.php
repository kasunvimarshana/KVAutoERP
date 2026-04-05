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
        'sku',
        'name',
        'attributes',
        'price',
        'cost',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active'  => 'bool',
        'price'      => 'float',
        'cost'       => 'float',
        'product_id' => 'int',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
