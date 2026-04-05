<?php declare(strict_types=1);
namespace Modules\POS\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class TerminalModel extends BaseModel {
    protected $table = 'pos_terminals';
    protected $fillable = ['tenant_id','name','code','warehouse_id','is_active'];
    protected $casts = ['is_active'=>'boolean','warehouse_id'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
