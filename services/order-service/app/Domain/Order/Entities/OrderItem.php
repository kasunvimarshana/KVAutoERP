<?php

declare(strict_types=1);

namespace App\Domain\Order\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Order Item Entity.
 */
class OrderItem extends Model
{
    use HasUuids;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'inventory_item_id',
        'sku',
        'name',
        'quantity',
        'unit_price',
        'total_price',
        'metadata',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'unit_price'  => 'decimal:4',
        'total_price' => 'decimal:4',
        'metadata'    => 'array',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
