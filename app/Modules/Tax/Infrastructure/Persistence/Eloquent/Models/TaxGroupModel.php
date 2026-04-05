<?php declare(strict_types=1);
namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class TaxGroupModel extends BaseModel {
    protected $table = 'tax_groups';
    protected $fillable = ['tenant_id','name','code','type','is_compound','is_active'];
    protected $casts = ['is_compound'=>'boolean','is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
