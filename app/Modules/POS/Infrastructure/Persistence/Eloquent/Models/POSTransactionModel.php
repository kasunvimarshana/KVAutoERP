<?php declare(strict_types=1);
namespace Modules\POS\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class POSTransactionModel extends BaseModel {
    protected $table = 'pos_transactions';
    protected $fillable = ['tenant_id','session_id','transaction_number','subtotal','tax_amount','discount_amount','total_amount','amount_paid','change','payment_method','status'];
    protected $casts = ['subtotal'=>'float','tax_amount'=>'float','discount_amount'=>'float','total_amount'=>'float','amount_paid'=>'float','change'=>'float','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
