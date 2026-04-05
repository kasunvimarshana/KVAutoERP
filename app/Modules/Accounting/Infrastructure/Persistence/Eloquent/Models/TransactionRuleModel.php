<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class TransactionRuleModel extends BaseModel {
    protected $table = 'transaction_rules';
    protected $fillable = ['tenant_id','name','apply_to','match_field','match_value','category_account_id','priority'];
    protected $casts = ['priority'=>'int','category_account_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
