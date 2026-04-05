<?php

declare(strict_types=1);

namespace Modules\Purchase\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLineModel extends Model
{
    protected $table = 'purchase_order_lines';

    protected $fillable = [
        'purchase_order_id', 'product_id', 'product_variant_id', 'description',
        'quantity', 'unit_price', 'discount_rate', 'tax_rate', 'total_price',
        'received_qty', 'unit_of_measure', 'notes',
    ];

    protected $casts = [
        'quantity'      => 'float',
        'unit_price'    => 'float',
        'discount_rate' => 'float',
        'tax_rate'      => 'float',
        'total_price'   => 'float',
        'received_qty'  => 'float',
    ];
}
