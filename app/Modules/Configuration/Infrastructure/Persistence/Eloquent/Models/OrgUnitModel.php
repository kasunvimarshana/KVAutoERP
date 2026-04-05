<?php declare(strict_types=1);
namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class OrgUnitModel extends BaseModel {
    protected $table = 'org_units';
    protected $fillable = ['tenant_id','name','code','type','parent_id','path','level','is_active'];
    protected $casts = ['is_active'=>'boolean','level'=>'int','parent_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
