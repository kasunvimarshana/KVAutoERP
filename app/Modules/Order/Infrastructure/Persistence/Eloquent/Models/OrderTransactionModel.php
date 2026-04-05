<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class OrderTransactionModel extends BaseModel
{
    protected $table = 'order_transactions';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'type',
        'amount',
        'currency',
        'payment_method',
        'status',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'order_id'   => 'int',
        'amount'     => 'float',
        'created_at' => 'datetime',
    ];
}
