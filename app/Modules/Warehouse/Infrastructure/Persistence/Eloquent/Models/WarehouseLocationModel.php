<?php declare(strict_types=1);
namespace Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class WarehouseLocationModel extends BaseModel {
    protected $table = 'warehouse_locations';
    protected $fillable = ['warehouse_id','name','code','type','parent_id','path','level','is_active','is_pickable','is_receivable'];
    protected $casts = ['level'=>'int','parent_id'=>'int','is_active'=>'boolean','is_pickable'=>'boolean','is_receivable'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
