<?php declare(strict_types=1);
namespace Modules\Order\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class OrderModel extends BaseModel {
    protected $table = 'orders';
    protected $fillable = ['tenant_id','order_number','type','status','party_id','warehouse_id','order_date','currency','subtotal','tax_amount','discount_amount','total_amount','notes'];
    protected $casts = ['subtotal'=>'float','tax_amount'=>'float','discount_amount'=>'float','total_amount'=>'float','order_date'=>'date','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
