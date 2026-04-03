<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ProductImageModel extends BaseModel
{
    protected $table = 'product_images';
    protected $fillable = ['tenant_id', 'product_id', 'uuid', 'name', 'file_path', 'mime_type', 'size', 'sort_order', 'is_primary', 'metadata'];
    protected $casts = ['id' => 'int', 'tenant_id' => 'int', 'product_id' => 'int', 'size' => 'int', 'sort_order' => 'int', 'is_primary' => 'boolean', 'metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
}
