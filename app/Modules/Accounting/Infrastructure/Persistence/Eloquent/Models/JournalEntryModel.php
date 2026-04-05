<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class JournalEntryModel extends BaseModel
{
    use HasTenant;

    protected $table = 'journal_entries';

    protected $fillable = [
        'tenant_id',
        'reference_no',
        'description',
        'entry_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'created_by' => 'int',
        'entry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLineModel::class, 'journal_entry_id');
    }
}
