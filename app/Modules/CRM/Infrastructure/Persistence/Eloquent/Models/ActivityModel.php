<?php declare(strict_types=1);
namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ActivityModel extends BaseModel {
    protected $table = 'activities';
    protected $fillable = ['tenant_id','type','subject','description','related_type','related_entity_type','status','scheduled_at','completed_at','assigned_to'];
    protected $casts = ['related_type'=>'int','scheduled_at'=>'datetime','completed_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
