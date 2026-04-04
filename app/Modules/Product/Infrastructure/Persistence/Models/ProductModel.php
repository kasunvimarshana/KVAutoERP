<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class ProductModel extends BaseModel
{
    protected $table = 'products';

    protected $fillable = [
        'tenant_id', 'name', 'sku', 'barcode', 'type', 'status', 'category_id',
        'description', 'short_description', 'weight', 'dimensions', 'images', 'tags',
        'is_taxable', 'tax_class', 'has_serial', 'has_batch', 'has_lot', 'is_serialized',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'id' => 'int', 'tenant_id' => 'int', 'category_id' => 'int', 'weight' => 'float',
        'dimensions' => 'array', 'images' => 'array', 'tags' => 'array',
        'is_taxable' => 'boolean', 'has_serial' => 'boolean', 'has_batch' => 'boolean',
        'has_lot' => 'boolean', 'is_serialized' => 'boolean',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategoryModel::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }
}
