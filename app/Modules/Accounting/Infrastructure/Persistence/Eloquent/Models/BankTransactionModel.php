<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class BankTransactionModel extends BaseModel {
    protected $table = 'bank_transactions';
    protected $fillable = ['bank_account_id','tenant_id','type','amount','transaction_date','description','status','source','account_id','reference'];
    protected $casts = ['amount'=>'float','transaction_date'=>'date','account_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
