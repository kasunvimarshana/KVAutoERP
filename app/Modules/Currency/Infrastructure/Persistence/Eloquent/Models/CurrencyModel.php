<?php declare(strict_types=1);
namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class CurrencyModel extends BaseModel {
    protected $table = 'currencies';
    protected $fillable = ['tenant_id','code','name','symbol','decimal_places','is_default','is_active'];
    protected $casts = ['decimal_places'=>'int','is_default'=>'boolean','is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
