<?php declare(strict_types=1);
namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class TaxGroupRateModel extends BaseModel {
    protected $table = 'tax_group_rates';
    protected $fillable = ['tax_group_id','name','rate','sequence'];
    protected $casts = ['rate'=>'float','sequence'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
