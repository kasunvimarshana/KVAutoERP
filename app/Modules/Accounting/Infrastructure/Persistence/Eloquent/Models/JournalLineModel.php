<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Journal lines intentionally do not soft-delete; deleting a posted entry
 * is handled by voiding the entry itself.
 */
class JournalLineModel extends Model
{
    protected $table = 'accounting_journal_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'description',
        'debit',
        'credit',
    ];

    protected $casts = [
        'id'               => 'int',
        'journal_entry_id' => 'int',
        'account_id'       => 'int',
        'debit'            => 'float',
        'credit'           => 'float',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntryModel::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'account_id');
    }
}
