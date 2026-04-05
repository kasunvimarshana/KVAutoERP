<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ValuationLayerModel extends BaseModel {
    protected $table = 'valuation_layers';
    protected $fillable = ['tenant_id','product_id','variant_id','warehouse_id','quantity','remaining_quantity','unit_cost','received_at','batch_number','lot_number','expiry_date'];
    protected $casts = ['quantity'=>'float','remaining_quantity'=>'float','unit_cost'=>'float','received_at'=>'datetime','expiry_date'=>'date','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
