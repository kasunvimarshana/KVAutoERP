<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class BankAccountModel extends BaseModel {
    protected $table = 'bank_accounts';
    protected $fillable = ['tenant_id','name','account_type','currency','gl_account_id','opening_balance','is_active'];
    protected $casts = ['opening_balance'=>'float','is_active'=>'boolean','gl_account_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
