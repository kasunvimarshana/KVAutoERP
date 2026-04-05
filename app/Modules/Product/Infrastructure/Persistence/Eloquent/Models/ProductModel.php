<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class ProductModel extends BaseModel
{
    use HasTenant;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'type',
        'status',
        'description',
        'short_description',
        'unit_of_measure',
        'weight',
        'dimensions',
        'images',
        'attributes',
        'tax_class',
        'cost_price',
        'selling_price',
        'is_serialized',
        'track_inventory',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'lead_time_days',
    ];

    protected $casts = [
        'id'              => 'int',
        'tenant_id'       => 'int',
        'category_id'     => 'int',
        'dimensions'      => 'array',
        'images'          => 'array',
        'attributes'      => 'array',
        'is_serialized'   => 'bool',
        'track_inventory' => 'bool',
        'cost_price'      => 'float',
        'selling_price'   => 'float',
        'weight'          => 'float',
        'min_stock_level' => 'float',
        'max_stock_level' => 'float',
        'reorder_point'   => 'float',
        'lead_time_days'  => 'int',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(ProductComponentModel::class, 'product_id');
    }
}
