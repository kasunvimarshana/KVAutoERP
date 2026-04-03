<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ProductVariationModel extends BaseModel
{
    protected $table = 'product_variations';
    protected $fillable = ['product_id', 'tenant_id', 'sku', 'name', 'price', 'currency', 'attribute_values', 'status', 'sort_order', 'metadata'];
    protected $casts = ['id' => 'int', 'product_id' => 'int', 'tenant_id' => 'int', 'price' => 'float', 'attribute_values' => 'array', 'metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
}
