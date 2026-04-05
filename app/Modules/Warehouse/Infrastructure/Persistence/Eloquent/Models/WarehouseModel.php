<?php declare(strict_types=1);
namespace Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class WarehouseModel extends BaseModel {
    protected $table = 'warehouses';
    protected $fillable = ['tenant_id','name','code','type','address','is_active','is_default'];
    protected $casts = ['is_active'=>'boolean','is_default'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
