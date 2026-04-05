<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class StockMovementModel extends BaseModel {
    protected $table = 'stock_movements';
    protected $fillable = ['tenant_id','product_id','variant_id','warehouse_id','location_id','type','quantity','unit_cost','reference','batch_number','lot_number','serial_number','expiry_date','moved_at','notes'];
    protected $casts = ['quantity'=>'float','unit_cost'=>'float','expiry_date'=>'date','moved_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
