<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class PaymentModel extends BaseModel
{
    use HasTenant;

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id',
        'reference_no',
        'payment_date',
        'amount',
        'currency',
        'payment_method',
        'status',
        'party_type',
        'party_id',
        'account_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'party_id'     => 'int',
        'account_id'   => 'int',
        'amount'       => 'float',
        'payment_date' => 'date',
        'metadata'     => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}
