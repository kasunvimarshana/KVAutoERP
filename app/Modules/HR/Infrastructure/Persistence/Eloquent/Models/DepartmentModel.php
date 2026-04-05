<?php declare(strict_types=1);
namespace Modules\HR\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class DepartmentModel extends BaseModel {
    protected $table = 'hr_departments';
    protected $fillable = ['tenant_id','name','code','parent_id','manager_id','is_active'];
    protected $casts = ['is_active'=>'boolean','parent_id'=>'int','manager_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
