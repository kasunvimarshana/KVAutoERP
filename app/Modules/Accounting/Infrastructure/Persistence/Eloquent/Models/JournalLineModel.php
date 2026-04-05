<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLineModel extends Model
{
    protected $table = 'journal_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'description',
        'debit',
        'credit',
        'metadata',
    ];

    protected $casts = [
        'id'               => 'int',
        'journal_entry_id' => 'int',
        'account_id'       => 'int',
        'debit'            => 'float',
        'credit'           => 'float',
        'metadata'         => 'array',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntryModel::class, 'journal_entry_id');
    }
}
