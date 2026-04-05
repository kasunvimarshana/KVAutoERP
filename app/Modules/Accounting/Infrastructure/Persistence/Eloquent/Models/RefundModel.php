<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class RefundModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_refunds';

    protected $fillable = [
        'tenant_id',
        'payment_id',
        'amount',
        'refund_date',
        'reason',
        'journal_entry_id',
    ];

    protected $casts = [
        'id'               => 'int',
        'tenant_id'        => 'int',
        'payment_id'       => 'int',
        'journal_entry_id' => 'int',
        'amount'           => 'float',
        'refund_date'      => 'date',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentModel::class, 'payment_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntryModel::class, 'journal_entry_id');
    }
}
