<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ProductCategoryModel extends BaseModel {
    protected $table = 'product_categories';
    protected $fillable = ['tenant_id','name','code','parent_id','path','level','is_active'];
    protected $casts = ['level'=>'int','parent_id'=>'int','is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
