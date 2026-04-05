<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class ProductVariantModel extends BaseModel
{
    use HasTenant;

    protected $table = 'product_variants';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'sku',
        'barcode',
        'name',
        'attributes',
        'cost_price',
        'selling_price',
        'weight',
        'is_active',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'product_id'   => 'int',
        'attributes'   => 'array',
        'is_active'    => 'bool',
        'cost_price'   => 'float',
        'selling_price' => 'float',
        'weight'       => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
