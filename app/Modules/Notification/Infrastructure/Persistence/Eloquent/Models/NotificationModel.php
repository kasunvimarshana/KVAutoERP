<?php declare(strict_types=1);
namespace Modules\Notification\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class NotificationModel extends BaseModel {
    protected $table = 'notifications';
    protected $fillable = ['tenant_id','user_id','channel','subject','body','status','error_message','sent_at','read_at'];
    protected $casts = ['sent_at'=>'datetime','read_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
