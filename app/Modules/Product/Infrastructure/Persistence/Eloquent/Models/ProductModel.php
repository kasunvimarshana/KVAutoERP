<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ProductModel extends BaseModel
{
    protected $table = 'products';
    protected $fillable = ['tenant_id', 'sku', 'name', 'price', 'currency', 'description', 'category', 'status', 'type', 'units_of_measure', 'product_attributes', 'attributes', 'metadata'];
    protected $casts = ['id' => 'int', 'tenant_id' => 'int', 'price' => 'float', 'units_of_measure' => 'array', 'product_attributes' => 'array', 'attributes' => 'array', 'metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
}
