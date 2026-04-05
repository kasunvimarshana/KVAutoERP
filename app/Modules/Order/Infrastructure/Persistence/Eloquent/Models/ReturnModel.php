<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class ReturnModel extends BaseModel
{
    use HasTenant;

    protected $table = 'returns';

    protected $fillable = [
        'tenant_id',
        'original_order_id',
        'type',
        'status',
        'contact_id',
        'warehouse_id',
        'reason',
        'restocking_fee',
        'credit_memo_amount',
        'notes',
        'quality_check',
        'metadata',
    ];

    protected $casts = [
        'id'                 => 'int',
        'tenant_id'          => 'int',
        'original_order_id'  => 'int',
        'contact_id'         => 'int',
        'warehouse_id'       => 'int',
        'restocking_fee'     => 'float',
        'credit_memo_amount' => 'float',
        'quality_check'      => 'boolean',
        'metadata'           => 'array',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];
}
