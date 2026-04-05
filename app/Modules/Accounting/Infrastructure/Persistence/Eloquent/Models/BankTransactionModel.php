<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BankTransactionModel extends BaseModel
{
    use HasTenant;

    protected $table = 'bank_transactions';

    protected $fillable = [
        'tenant_id',
        'bank_account_id',
        'date',
        'description',
        'amount',
        'type',
        'status',
        'category',
        'account_id',
        'reference_no',
        'source',
        'metadata',
    ];

    protected $casts = [
        'id'              => 'int',
        'tenant_id'       => 'int',
        'bank_account_id' => 'int',
        'account_id'      => 'int',
        'date'            => 'date',
        'amount'          => 'float',
        'metadata'        => 'array',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];
}
