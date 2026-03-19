<?php

declare(strict_types=1);

namespace App\Models;

use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * StockLedger — the immutable, append-only record of every stock movement.
 *
 * Records are NEVER updated or deleted after creation. Historical stock
 * balances can be reconstructed by replaying ledger entries up to any
 * point in time.
 *
 * Each entry carries a signed qty_change (positive = in, negative = out)
 * and a snapshot of the qty_after the movement was applied.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $product_id
 * @property string      $warehouse_id
 * @property string|null $bin_id
 * @property string|null $lot_id
 * @property string      $transaction_type
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $idempotency_key
 * @property string      $qty_change       BCMath string, 4dp — signed
 * @property string      $qty_after        BCMath string, 4dp — snapshot
 * @property string      $unit_cost
 * @property string      $total_cost
 * @property string      $currency
 * @property string|null $uom_id
 * @property string|null $notes
 * @property array|null  $metadata
 * @property string|null $performed_by
 * @property \Carbon\Carbon $transacted_at
 */
final class StockLedger extends TenantAwareModel
{
    /** @var string */
    protected $table = 'stock_ledger';

    /** @var array<string, string> */
    protected $casts = [
        'qty_change'     => 'string',
        'qty_after'      => 'string',
        'unit_cost'      => 'string',
        'total_cost'     => 'string',
        'metadata'       => 'array',
        'transacted_at'  => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /** All supported transaction types. */
    public const TRANSACTION_TYPES = [
        'receive',
        'dispatch',
        'adjustment',
        'transfer_in',
        'transfer_out',
        'reservation',
        'reservation_release',
        'cycle_count',
        'opening_balance',
        'write_off',
        'return_in',
        'return_out',
        'scrap',
    ];

    /** Inflow transaction types (positive qty_change). */
    public const INFLOW_TYPES = [
        'receive', 'transfer_in', 'reservation_release',
        'return_in', 'adjustment', 'cycle_count', 'opening_balance',
    ];

    /** Outflow transaction types (negative qty_change). */
    public const OUTFLOW_TYPES = [
        'dispatch', 'transfer_out', 'reservation',
        'return_out', 'write_off', 'scrap',
    ];

    /**
     * Return whether this entry represents a stock inflow.
     *
     * @return bool
     */
    public function isInflow(): bool
    {
        return bccomp($this->qty_change, '0', 4) > 0;
    }
}
