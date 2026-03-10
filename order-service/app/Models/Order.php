<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    // Status constants
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED    = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'failure_reason',
        'payment_id',
        'saga_state',
    ];

    protected $casts = [
        'unit_price'  => 'float',
        'total_price' => 'float',
        'quantity'    => 'integer',
    ];
}
