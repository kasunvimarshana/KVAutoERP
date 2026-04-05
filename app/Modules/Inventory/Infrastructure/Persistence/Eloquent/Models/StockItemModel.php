<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class StockItemModel extends BaseModel {
    protected $table = 'stock_items';
    protected $fillable = ['tenant_id','product_id','variant_id','warehouse_id','location_id','quantity','reserved_quantity','available_quantity','unit'];
    protected $casts = ['quantity'=>'float','reserved_quantity'=>'float','available_quantity'=>'float','product_id'=>'int','warehouse_id'=>'int','variant_id'=>'int','location_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
