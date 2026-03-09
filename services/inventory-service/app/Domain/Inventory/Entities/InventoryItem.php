<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Inventory Item Entity.
 *
 * Represents a product/item in a tenant's inventory.
 * Implements tenant isolation via tenant_id scoping.
 */
class InventoryItem extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'inventory_items';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'warehouse_id',
        'sku',
        'name',
        'description',
        'quantity',
        'reserved_quantity',
        'reorder_point',
        'reorder_quantity',
        'unit_cost',
        'unit_price',
        'unit_of_measure',
        'status',
        'metadata',
        'tags',
    ];

    protected $casts = [
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_point'     => 'integer',
        'reorder_quantity'  => 'integer',
        'unit_cost'         => 'decimal:4',
        'unit_price'        => 'decimal:4',
        'metadata'          => 'array',
        'tags'              => 'array',
    ];

    // =========================================================================
    // Business Logic Methods
    // =========================================================================

    /**
     * Get the available quantity (total minus reserved).
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    /**
     * Check if this item needs reordering.
     */
    public function needsReorder(): bool
    {
        return $this->available_quantity <= $this->reorder_point;
    }

    /**
     * Check if the requested quantity is available.
     *
     * @param  int $quantity
     * @return bool
     */
    public function hasAvailableStock(int $quantity): bool
    {
        return $this->available_quantity >= $quantity;
    }

    /**
     * Reserve stock for an order.
     *
     * @param  int $quantity
     * @return void
     * @throws \DomainException
     */
    public function reserveStock(int $quantity): void
    {
        if (!$this->hasAvailableStock($quantity)) {
            throw new \DomainException(
                "Insufficient stock for item [{$this->sku}]. Available: {$this->available_quantity}, Requested: {$quantity}",
            );
        }

        $this->increment('reserved_quantity', $quantity);
    }

    /**
     * Release previously reserved stock.
     *
     * @param  int $quantity
     * @return void
     */
    public function releaseStock(int $quantity): void
    {
        $releaseAmount = min($quantity, $this->reserved_quantity);
        $this->decrement('reserved_quantity', $releaseAmount);
    }

    /**
     * Deduct stock when an order is fulfilled.
     *
     * @param  int $quantity
     * @return void
     */
    public function deductStock(int $quantity): void
    {
        $this->decrement('quantity', $quantity);
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Domain\Category\Entities\Category::class, 'category_id');
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Domain\Warehouse\Entities\Warehouse::class, 'warehouse_id');
    }

    public function movements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class, 'inventory_item_id');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeLowStock(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= reorder_point');
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }
}
