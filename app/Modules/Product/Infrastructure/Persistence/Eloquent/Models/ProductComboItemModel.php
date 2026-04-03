<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ProductComboItemModel extends BaseModel
{
    protected $table = 'product_combo_items';
    protected $fillable = ['product_id', 'tenant_id', 'component_product_id', 'quantity', 'price_override', 'sort_order', 'metadata'];
    protected $casts = ['id' => 'int', 'product_id' => 'int', 'tenant_id' => 'int', 'component_product_id' => 'int', 'quantity' => 'float', 'price_override' => 'float', 'sort_order' => 'int', 'metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
}
