<?php declare(strict_types=1);
namespace Modules\Notification\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class NotificationTemplateModel extends BaseModel {
    protected $table = 'notification_templates';
    protected $fillable = ['tenant_id','name','event','channel','subject','body','is_active'];
    protected $casts = ['is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
