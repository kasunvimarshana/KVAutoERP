<?php declare(strict_types=1);
namespace Modules\Customer\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class CustomerModel extends BaseModel {
    protected $table='customers';
    protected $fillable=['tenant_id','user_id','name','code','email','phone','billing_address','shipping_address','date_of_birth','loyalty_tier','credit_limit','payment_terms','currency','tax_number','status','type','attributes','metadata'];
    protected $casts=['id'=>'int','tenant_id'=>'int','user_id'=>'int','credit_limit'=>'float','billing_address'=>'array','shipping_address'=>'array','attributes'=>'array','metadata'=>'array','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime'];
}
