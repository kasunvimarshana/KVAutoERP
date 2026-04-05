<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class JournalEntryModel extends BaseModel {
    protected $table = 'journal_entries';
    protected $fillable = ['tenant_id','reference','description','transaction_date','status','currency','created_by','posted_at'];
    protected $casts = ['transaction_date'=>'date','posted_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
