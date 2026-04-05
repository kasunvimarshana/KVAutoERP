<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class PaymentModel extends BaseModel {
    protected $table = 'payments';
    protected $fillable = ['tenant_id','type','party_id','amount','currency','payment_date','method','reference','status'];
    protected $casts = ['amount'=>'float','payment_date'=>'date','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
