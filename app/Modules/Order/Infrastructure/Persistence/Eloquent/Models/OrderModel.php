<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class OrderModel extends BaseModel
{
    use HasTenant;

    protected $table = 'orders';

    protected $fillable = [
        'tenant_id',
        'order_number',
        'type',
        'status',
        'contact_id',
        'warehouse_id',
        'currency',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'total_amount',
        'notes',
        'shipping_address',
        'billing_address',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'id'               => 'int',
        'tenant_id'        => 'int',
        'contact_id'       => 'int',
        'warehouse_id'     => 'int',
        'created_by'       => 'int',
        'subtotal'         => 'float',
        'discount_amount'  => 'float',
        'tax_amount'       => 'float',
        'shipping_amount'  => 'float',
        'total_amount'     => 'float',
        'shipping_address' => 'array',
        'billing_address'  => 'array',
        'metadata'         => 'array',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];
}
