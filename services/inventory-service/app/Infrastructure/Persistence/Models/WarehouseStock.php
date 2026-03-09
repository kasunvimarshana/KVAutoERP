<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for the warehouse_stocks table.
 *
 * @property string  $id
 * @property string  $tenant_id
 * @property string  $warehouse_id
 * @property string  $product_id
 * @property int     $quantity
 * @property int     $reserved_quantity
 */
class WarehouseStock extends Model
{
    use HasUuids;

    protected $table = 'warehouse_stocks';

    protected $fillable = [
        'id',
        'tenant_id',
        'warehouse_id',
        'product_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
    ];

    protected $attributes = [
        'quantity'          => 0,
        'reserved_quantity' => 0,
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }
}
