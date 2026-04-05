<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ProductComponentModel extends BaseModel {
    protected $table = 'product_components';
    protected $fillable = ['parent_product_id','component_product_id','quantity','unit'];
    protected $casts = ['quantity'=>'float','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
