<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class RefundModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'refunds';

    protected $fillable = [
        'tenant_id', 'refund_number', 'refund_date', 'amount', 'currency',
        'payment_method', 'account_id', 'reference', 'notes', 'status', 'original_payment_id',
    ];

    protected $casts = [
        'refund_date' => 'date',
        'amount'      => 'float',
    ];
}
