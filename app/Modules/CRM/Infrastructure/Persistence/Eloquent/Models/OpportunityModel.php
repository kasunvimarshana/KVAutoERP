<?php declare(strict_types=1);
namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class OpportunityModel extends BaseModel {
    protected $table = 'opportunities';
    protected $fillable = ['tenant_id','title','contact_id','stage','value','currency','probability','assigned_to','expected_close_date'];
    protected $casts = ['value'=>'float','probability'=>'float','expected_close_date'=>'date','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
