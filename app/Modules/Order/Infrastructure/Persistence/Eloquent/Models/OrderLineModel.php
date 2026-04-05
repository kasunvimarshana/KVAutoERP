<?php declare(strict_types=1);
namespace Modules\Order\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class OrderLineModel extends BaseModel {
    protected $table = 'order_lines';
    protected $fillable = ['order_id','product_id','variant_id','quantity','unit_price','tax_amount','discount_amount','line_total','batch_number','lot_number','serial_number'];
    protected $casts = ['quantity'=>'float','unit_price'=>'float','tax_amount'=>'float','discount_amount'=>'float','line_total'=>'float','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
