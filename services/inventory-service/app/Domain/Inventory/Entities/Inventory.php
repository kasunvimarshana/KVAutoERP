<?php

namespace App\Domain\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $table = 'inventories';

    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'description',
        'quantity',
        'reserved_quantity',
        'unit_cost',
        'unit_price',
        'category',
        'location',
        'min_stock_level',
        'max_stock_level',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata'          => 'array',
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
        'unit_cost'         => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'min_stock_level'   => 'integer',
        'max_stock_level'   => 'integer',
    ];

    protected $attributes = [
        'quantity'          => 0,
        'reserved_quantity' => 0,
        'min_stock_level'   => 0,
        'max_stock_level'   => 9999,
        'status'            => 'active',
    ];

    // ─── Business logic ───────────────────────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->getAvailableQuantity() <= 0;
    }

    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function canReserve(int $amount): bool
    {
        return $this->getAvailableQuantity() >= $amount;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_stock_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= 0');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
