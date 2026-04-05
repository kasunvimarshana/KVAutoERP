<?php declare(strict_types=1);
namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class PriceListModel extends BaseModel {
    protected $table = 'price_lists';
    protected $fillable = ['tenant_id','name','code','currency','is_default','is_active'];
    protected $casts = ['is_default'=>'boolean','is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
