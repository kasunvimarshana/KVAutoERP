<?php declare(strict_types=1);
namespace Modules\HR\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class LeaveRequestModel extends BaseModel {
    protected $table = 'hr_leave_requests';
    protected $fillable = ['tenant_id','employee_id','leave_type','start_date','end_date','days','status','reason','approved_by'];
    protected $casts = ['days'=>'float','start_date'=>'date','end_date'=>'date','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
