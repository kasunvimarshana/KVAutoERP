<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class RefundModel extends BaseModel
{
    use HasTenant;

    protected $table = 'refunds';

    protected $fillable = [
        'tenant_id',
        'reference_no',
        'refund_date',
        'amount',
        'currency',
        'payment_method',
        'status',
        'payment_id',
        'reason',
        'account_id',
        'notes',
    ];

    protected $casts = [
        'id'          => 'int',
        'tenant_id'   => 'int',
        'payment_id'  => 'int',
        'account_id'  => 'int',
        'amount'      => 'float',
        'refund_date' => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];
}
