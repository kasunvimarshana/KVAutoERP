<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class PaymentModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_payments';

    protected $fillable = [
        'tenant_id',
        'reference_no',
        'date',
        'amount',
        'currency_code',
        'payment_method',
        'bank_account_id',
        'journal_entry_id',
        'payable_type',
        'payable_id',
        'notes',
    ];

    protected $casts = [
        'id'               => 'int',
        'tenant_id'        => 'int',
        'bank_account_id'  => 'int',
        'journal_entry_id' => 'int',
        'payable_id'       => 'int',
        'amount'           => 'float',
        'date'             => 'date',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccountModel::class, 'bank_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntryModel::class, 'journal_entry_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(RefundModel::class, 'payment_id');
    }
}
