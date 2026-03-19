<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReorderRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'min_quantity', // Reorder Point
        'max_quantity', // Target Stock Level
        'reorder_quantity', // Standard quantity to order
        'status', // Active, Paused
    ];

    protected $casts = [
        'min_quantity' => 'decimal:10',
        'max_quantity' => 'decimal:10',
        'reorder_quantity' => 'decimal:10',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
