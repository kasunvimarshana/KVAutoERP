<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class JournalLineModel extends BaseModel {
    protected $table = 'journal_lines';
    protected $fillable = ['journal_entry_id','account_id','debit_amount','credit_amount','description'];
    protected $casts = ['debit_amount'=>'float','credit_amount'=>'float','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
