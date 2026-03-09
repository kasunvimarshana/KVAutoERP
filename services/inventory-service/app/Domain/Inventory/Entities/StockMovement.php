<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Stock Movement Entity.
 *
 * Immutable audit trail of all stock changes.
 * Implements Event Sourcing pattern for inventory history.
 */
class StockMovement extends Model
{
    use HasUuids;

    protected $table = 'stock_movements';

    /**
     * Stock movements are immutable - no updates allowed.
     */
    public $timestamps = true;

    protected $fillable = [
        'tenant_id',
        'inventory_item_id',
        'type',            // in|out|adjustment|reservation|release|transfer
        'quantity',
        'before_quantity',
        'after_quantity',
        'reference_type',  // order|purchase|adjustment|transfer
        'reference_id',
        'notes',
        'performed_by',
        'metadata',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'before_quantity' => 'integer',
        'after_quantity'  => 'integer',
        'metadata'        => 'array',
    ];

    public function inventoryItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
}
