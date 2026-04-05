<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class CycleCountModel extends BaseModel {
    protected $table = 'cycle_counts';
    protected $fillable = ['tenant_id','warehouse_id','status','reference','scheduled_at','completed_at'];
    protected $casts = ['scheduled_at'=>'datetime','completed_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
