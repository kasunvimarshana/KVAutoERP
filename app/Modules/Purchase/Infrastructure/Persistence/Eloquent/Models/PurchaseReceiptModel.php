<?php

declare(strict_types=1);

namespace Modules\Purchase\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class PurchaseReceiptModel extends BaseModel
{
    use HasTenant;

    protected $table = 'purchase_receipts';

    protected $fillable = [
        'tenant_id', 'purchase_order_id', 'reference_no', 'receipt_date',
        'warehouse_id', 'location_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];
}
