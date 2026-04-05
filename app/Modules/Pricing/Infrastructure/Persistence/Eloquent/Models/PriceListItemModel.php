<?php declare(strict_types=1);
namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class PriceListItemModel extends BaseModel {
    protected $table = 'price_list_items';
    protected $fillable = ['price_list_id','product_id','price_type','price','min_quantity','valid_from','valid_to'];
    protected $casts = ['price'=>'float','min_quantity'=>'float','valid_from'=>'date','valid_to'=>'date','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
