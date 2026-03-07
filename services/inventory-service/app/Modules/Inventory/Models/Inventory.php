<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'product_sku',
        'quantity',
        'reserved_quantity',
        'warehouse_location',
        'reorder_level',
        'reorder_quantity',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_level'     => 'integer',
        'reorder_quantity'  => 'integer',
        'unit_cost'         => 'decimal:2',
    ];

    /**
     * Get available quantity (total - reserved).
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Check if stock is below reorder level.
     */
    public function needsReorder(): bool
    {
        return $this->available_quantity <= $this->reorder_level;
    }

    /**
     * Scope for low-stock items.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= reorder_level');
    }

    /**
     * Scope for filtering by warehouse location.
     */
    public function scopeInWarehouse(Builder $query, string $location): Builder
    {
        return $query->where('warehouse_location', $location);
    }

    /**
     * Scope for items with available stock.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity - reserved_quantity) > 0');
    }
}
