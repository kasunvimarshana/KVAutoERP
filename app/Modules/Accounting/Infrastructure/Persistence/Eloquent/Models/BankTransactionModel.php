<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BankTransactionModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_bank_transactions';

    protected $fillable = [
        'tenant_id',
        'bank_account_id',
        'date',
        'amount',
        'type',
        'description',
        'reference',
        'source',
        'status',
        'account_id',
        'transaction_rule_id',
        'metadata',
    ];

    protected $casts = [
        'id'                  => 'int',
        'tenant_id'           => 'int',
        'bank_account_id'     => 'int',
        'amount'              => 'float',
        'account_id'          => 'int',
        'transaction_rule_id' => 'int',
        'date'                => 'date',
        'metadata'            => 'array',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccountModel::class, 'bank_account_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'account_id');
    }

    public function transactionRule(): BelongsTo
    {
        return $this->belongsTo(TransactionRuleModel::class, 'transaction_rule_id');
    }
}
