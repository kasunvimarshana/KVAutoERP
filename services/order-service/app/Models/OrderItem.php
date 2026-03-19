<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'uom_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:10',
        'unit_price' => 'decimal:10',
        'total_price' => 'decimal:10',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
