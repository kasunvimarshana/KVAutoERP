<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class AccountModel extends BaseModel {
    protected $table = 'accounts';
    protected $fillable = ['tenant_id','code','name','type','sub_type','parent_id','is_active','normal_balance','description'];
    protected $casts = ['is_active'=>'boolean','parent_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
