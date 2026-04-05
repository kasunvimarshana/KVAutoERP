<?php declare(strict_types=1);
namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class ExchangeRateModel extends BaseModel {
    protected $table = 'exchange_rates';
    protected $fillable = ['tenant_id','from_currency','to_currency','rate','effective_date'];
    protected $casts = ['rate'=>'float','effective_date'=>'date','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
