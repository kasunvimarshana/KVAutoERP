<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ProductModel extends BaseModel {
    protected $table = 'products';
    protected $fillable = ['tenant_id','sku','name','type','category_id','cost_price','sale_price','currency','description','is_active','is_taxable','tax_group_id','barcode','unit'];
    protected $casts = ['cost_price'=>'float','sale_price'=>'float','is_active'=>'boolean','is_taxable'=>'boolean','category_id'=>'int','tax_group_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
