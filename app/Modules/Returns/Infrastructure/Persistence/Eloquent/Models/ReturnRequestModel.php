<?php
declare(strict_types=1);
namespace Modules\Returns\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ReturnRequestModel extends BaseModel {
    protected $table = 'return_requests';
    protected $fillable = ['tenant_id','return_type','reference_id','return_number','status','reason','notes','processed_by','processed_at'];
    protected $casts = ['id'=>'int','tenant_id'=>'int','reference_id'=>'int','processed_by'=>'int',
        'processed_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime'];
    public function lines() { return $this->hasMany(ReturnLineModel::class,'return_request_id'); }
}
