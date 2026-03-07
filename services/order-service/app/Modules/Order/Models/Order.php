<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'total_amount',
        'currency',
        'shipping_address',
        'billing_address',
        'notes',
        'saga_state',
    ];

    protected $casts = [
        'total_amount'     => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address'  => 'array',
        'saga_state'       => 'array',
    ];

    const STATUS_PENDING    = 'pending';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED    = 'shipped';
    const STATUS_DELIVERED  = 'delivered';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_FAILED     = 'failed';

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
