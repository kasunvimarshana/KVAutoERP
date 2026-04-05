<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class JournalEntryModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_journal_entries';

    protected $fillable = [
        'tenant_id',
        'reference_no',
        'date',
        'description',
        'status',
        'type',
        'created_by',
        'posted_at',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'created_by' => 'int',
        'date'       => 'date',
        'posted_at'  => 'datetime',
        'voided_at'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLineModel::class, 'journal_entry_id');
    }
}
