<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class PaymentModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id', 'payment_number', 'payment_date', 'amount', 'currency',
        'payment_method', 'from_account_id', 'to_account_id', 'reference',
        'notes', 'status', 'journal_entry_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'float',
    ];
}
