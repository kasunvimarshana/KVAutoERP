<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class JournalLineModel extends Model
{
    use HasUuid;

    protected $table = 'journal_lines';

    protected $fillable = [
        'tenant_id', 'journal_entry_id', 'account_id', 'debit', 'credit', 'description',
    ];

    protected $casts = [
        'debit'  => 'float',
        'credit' => 'float',
    ];
}
