<?php declare(strict_types=1);
namespace Modules\Order\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ReturnModel extends BaseModel {
    protected $table = 'returns';
    protected $fillable = ['tenant_id','original_order_id','type','status','reason','refund_amount','condition','restock_items','processed_at'];
    protected $casts = ['refund_amount'=>'float','restock_items'=>'boolean','processed_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
