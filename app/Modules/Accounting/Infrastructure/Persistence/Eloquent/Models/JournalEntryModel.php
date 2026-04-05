<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class JournalEntryModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'journal_entries';

    protected $fillable = [
        'tenant_id', 'entry_number', 'date', 'description', 'reference',
        'status', 'total_debit', 'total_credit', 'created_by',
    ];

    protected $casts = [
        'date'         => 'date',
        'total_debit'  => 'float',
        'total_credit' => 'float',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLineModel::class, 'journal_entry_id');
    }
}
