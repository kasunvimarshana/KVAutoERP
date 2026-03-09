<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Inventory\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent model for the products table.
 *
 * @property string           $id
 * @property string           $tenant_id
 * @property string|null      $category_id
 * @property string           $sku
 * @property string           $name
 * @property string           $description
 * @property float            $price
 * @property float            $cost_price
 * @property string           $currency
 * @property int              $stock_quantity
 * @property int              $reserved_quantity
 * @property int              $min_stock_level
 * @property int              $max_stock_level
 * @property string           $unit
 * @property string|null      $barcode
 * @property ProductStatus    $status
 * @property bool             $is_active
 * @property array            $tags
 * @property array            $attributes
 * @property \Carbon\Carbon   $created_at
 * @property \Carbon\Carbon   $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Product extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'id',
        'tenant_id',
        'category_id',
        'sku',
        'name',
        'description',
        'price',
        'cost_price',
        'currency',
        'stock_quantity',
        'reserved_quantity',
        'min_stock_level',
        'max_stock_level',
        'unit',
        'barcode',
        'status',
        'is_active',
        'tags',
        'attributes',
    ];

    protected $casts = [
        'price'             => 'decimal:2',
        'cost_price'        => 'decimal:2',
        'stock_quantity'    => 'integer',
        'reserved_quantity' => 'integer',
        'min_stock_level'   => 'integer',
        'max_stock_level'   => 'integer',
        'is_active'         => 'boolean',
        'tags'              => 'array',
        'attributes'        => 'array',
        'status'            => ProductStatus::class,
    ];

    protected $attributes = [
        'description'       => '',
        'reserved_quantity' => 0,
        'min_stock_level'   => 0,
        'max_stock_level'   => 0,
        'unit'              => 'unit',
        'is_active'         => true,
        'tags'              => '[]',
        'attributes'        => '[]',
        'currency'          => 'USD',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class, 'product_id');
    }

    // ─── Query Scopes ─────────────────────────────────────────────────────────

    /** Scope to a specific tenant. */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /** Only active products. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', ProductStatus::ACTIVE->value);
    }

    /** Products whose stock is at or below their min_stock_level. */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('stock_quantity <= min_stock_level AND min_stock_level > 0');
    }

    /** Products with zero stock. */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', 0);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Available quantity (total minus reserved). */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_quantity);
    }
}
