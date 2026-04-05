<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class CycleCountLineModel extends BaseModel {
    protected $table = 'cycle_count_lines';
    protected $fillable = ['cycle_count_id','product_id','location_id','system_quantity','counted_quantity','variance'];
    protected $casts = ['system_quantity'=>'float','counted_quantity'=>'float','variance'=>'float','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
