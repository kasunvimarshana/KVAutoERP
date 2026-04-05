<?php declare(strict_types=1);
namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ContactModel extends BaseModel {
    protected $table = 'contacts';
    protected $fillable = ['tenant_id','type','name','email','phone','company','address','is_active','metadata'];
    protected $casts = ['is_active'=>'boolean','metadata'=>'array','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
