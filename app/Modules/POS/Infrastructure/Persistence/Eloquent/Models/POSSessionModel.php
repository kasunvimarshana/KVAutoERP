<?php declare(strict_types=1);
namespace Modules\POS\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class POSSessionModel extends BaseModel {
    protected $table = 'pos_sessions';
    protected $fillable = ['tenant_id','terminal_id','user_id','opening_cash','closing_cash','status','opened_at','closed_at'];
    protected $casts = ['opening_cash'=>'float','closing_cash'=>'float','opened_at'=>'datetime','closed_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
