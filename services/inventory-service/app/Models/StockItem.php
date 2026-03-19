<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * StockItem — aggregate of current on-hand inventory for a product at a
 * specific warehouse/bin/lot combination.
 *
 * This record is a derived projection maintained by the ledger processor.
 * Pessimistic locking (lockForUpdate) MUST be used when deducting stock
 * to prevent race conditions.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string $product_id
 * @property string $warehouse_id
 * @property string|null $bin_id
 * @property string|null $lot_id
 * @property string $qty_on_hand      BCMath string, 4dp
 * @property string $qty_reserved     BCMath string, 4dp
 * @property string $qty_available    Computed: on_hand - reserved
 * @property string|null $uom_id
 * @property string $unit_cost        BCMath string, 4dp
 * @property int    $version          Optimistic lock version
 */
final class StockItem extends TenantAwareModel
{
    /** @var string */
    protected $table = 'stock_items';

    /** @var array<string, string> */
    protected $casts = [
        'qty_on_hand'   => 'string',
        'qty_reserved'  => 'string',
        'qty_available' => 'string',
        'unit_cost'     => 'string',
        'version'       => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * The warehouse this stock is located in.
     *
     * @return BelongsTo<Warehouse, StockItem>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * The bin within the warehouse (optional).
     *
     * @return BelongsTo<WarehouseBin, StockItem>
     */
    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }

    /**
     * The lot/serial/batch this stock is attributed to (optional).
     *
     * @return BelongsTo<StockLot, StockItem>
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(StockLot::class, 'lot_id');
    }

    /**
     * Return whether there is sufficient available quantity.
     *
     * @param  string  $qty  Required quantity as BCMath string.
     * @return bool
     */
    public function hasSufficientStock(string $qty): bool
    {
        return bccomp($this->qty_available, $qty, 4) >= 0;
    }
}
