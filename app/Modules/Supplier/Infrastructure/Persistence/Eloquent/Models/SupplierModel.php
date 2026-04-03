<?php declare(strict_types=1);
namespace Modules\Supplier\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class SupplierModel extends BaseModel {
    protected $table='suppliers';
    protected $fillable=['tenant_id','user_id','name','code','email','phone','address','contact_person','payment_terms','currency','tax_number','status','type','attributes','metadata'];
    protected $casts=['id'=>'int','tenant_id'=>'int','user_id'=>'int','address'=>'array','contact_person'=>'array','attributes'=>'array','metadata'=>'array','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime'];
}
