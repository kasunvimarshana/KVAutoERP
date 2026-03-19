<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * StockTransfer — header record for a stock movement between warehouses or bins.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $organization_id
 * @property string|null $transfer_number
 * @property string      $status
 * @property string      $transfer_type
 * @property string      $from_warehouse_id
 * @property string|null $from_bin_id
 * @property string      $to_warehouse_id
 * @property string|null $to_bin_id
 * @property \Carbon\Carbon|null $requested_at
 * @property \Carbon\Carbon|null $dispatched_at
 * @property \Carbon\Carbon|null $received_at
 * @property string|null $notes
 * @property array|null  $metadata
 */
final class StockTransfer extends TenantAwareModel
{
    /** @var string */
    protected $table = 'stock_transfers';

    /** @var array<string, string> */
    protected $casts = [
        'requested_at'  => 'datetime',
        'dispatched_at' => 'datetime',
        'received_at'   => 'datetime',
        'metadata'      => 'array',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /** Valid transfer statuses. */
    public const STATUSES = ['draft', 'in_transit', 'completed', 'cancelled'];

    /** Valid transfer types. */
    public const TYPES = ['internal', 'cross_branch', 'drop_ship'];

    /**
     * The source warehouse.
     *
     * @return BelongsTo<Warehouse, StockTransfer>
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * The destination warehouse.
     *
     * @return BelongsTo<Warehouse, StockTransfer>
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Line items for this transfer.
     *
     * @return HasMany<StockTransferLine>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(StockTransferLine::class, 'transfer_id');
    }

    /**
     * Return whether this transfer can be dispatched.
     *
     * @return bool
     */
    public function canDispatch(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Return whether this transfer can be received.
     *
     * @return bool
     */
    public function canReceive(): bool
    {
        return $this->status === 'in_transit';
    }
}
