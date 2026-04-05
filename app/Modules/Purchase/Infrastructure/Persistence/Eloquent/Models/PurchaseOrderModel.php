<?php

declare(strict_types=1);

namespace Modules\Purchase\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class PurchaseOrderModel extends BaseModel
{
    use HasTenant;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'tenant_id', 'contact_id', 'reference_no', 'order_date', 'expected_date',
        'warehouse_id', 'status', 'currency_code', 'exchange_rate',
        'subtotal', 'discount_amount', 'tax_amount', 'total', 'notes', 'created_by',
    ];

    protected $casts = [
        'order_date'     => 'date',
        'expected_date'  => 'date',
        'exchange_rate'  => 'float',
        'subtotal'       => 'float',
        'discount_amount'=> 'float',
        'tax_amount'     => 'float',
        'total'          => 'float',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];
}
