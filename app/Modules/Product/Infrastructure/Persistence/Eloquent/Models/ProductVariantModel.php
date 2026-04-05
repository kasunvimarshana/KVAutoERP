<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ProductVariantModel extends BaseModel {
    protected $table = 'product_variants';
    protected $fillable = ['product_id','sku','name','attributes','price_override','cost_override','is_active'];
    protected $casts = ['attributes'=>'array','price_override'=>'float','cost_override'=>'float','is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
