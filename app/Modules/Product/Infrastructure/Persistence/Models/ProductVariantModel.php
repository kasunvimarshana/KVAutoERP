<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class ProductVariantModel extends BaseModel
{
    protected $table = 'product_variants';

    protected $fillable = [
        'tenant_id', 'product_id', 'name', 'sku', 'barcode', 'attributes',
        'price', 'cost', 'weight', 'is_active', 'stock_management', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'id' => 'int', 'tenant_id' => 'int', 'product_id' => 'int',
        'attributes' => 'array', 'price' => 'float', 'cost' => 'float', 'weight' => 'float',
        'is_active' => 'boolean', 'stock_management' => 'boolean',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
