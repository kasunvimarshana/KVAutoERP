<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class BankTransactionModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'bank_transactions';

    protected $fillable = [
        'tenant_id', 'bank_account_id', 'date', 'description', 'amount',
        'type', 'status', 'source', 'category_id', 'journal_entry_id',
        'reference', 'metadata',
    ];

    protected $casts = [
        'date'     => 'date',
        'amount'   => 'float',
        'metadata' => 'array',
    ];
}
