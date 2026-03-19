<?php

declare(strict_types=1);

namespace App\Models;

use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * StockReservation — a hold on quantity to prevent over-selling.
 *
 * When stock is reserved, qty_reserved on the corresponding StockItem
 * increases and qty_available decreases accordingly.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $product_id
 * @property string      $warehouse_id
 * @property string|null $bin_id
 * @property string|null $lot_id
 * @property string      $reference_type
 * @property string      $reference_id
 * @property string      $qty_reserved     BCMath string, 4dp
 * @property string      $qty_fulfilled    BCMath string, 4dp
 * @property string      $qty_remaining    Computed
 * @property string      $status
 * @property \Carbon\Carbon|null $expires_at
 * @property string|null $notes
 */
final class StockReservation extends TenantAwareModel
{
    /** @var string */
    protected $table = 'stock_reservations';

    /** @var array<string, string> */
    protected $casts = [
        'qty_reserved'  => 'string',
        'qty_fulfilled' => 'string',
        'qty_remaining' => 'string',
        'expires_at'    => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /** Valid reservation statuses. */
    public const STATUSES = ['active', 'partially_fulfilled', 'fulfilled', 'cancelled'];

    /**
     * Return whether this reservation is still active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'partially_fulfilled';
    }

    /**
     * Return whether this reservation has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
