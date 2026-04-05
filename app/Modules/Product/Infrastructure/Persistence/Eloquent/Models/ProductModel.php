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
        'sku',
        'name',
        'type',
        'category_id',
        'description',
        'is_active',
        'unit_of_measure',
        'weight',
        'dimensions',
        'images',
        'metadata',
    ];

    protected $casts = [
        'is_active'  => 'bool',
        'weight'     => 'float',
        'dimensions' => 'array',
        'images'     => 'array',
        'metadata'   => 'array',
        'category_id'=> 'int',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }

    public function components(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductComponentModel::class, 'product_id');
    }
}
