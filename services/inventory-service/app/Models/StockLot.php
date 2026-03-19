<?php

declare(strict_types=1);

namespace App\Models;

use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * StockLot — tracks serial numbers, lot numbers, and batch identifiers.
 *
 * Supports FEFO (First-Expired, First-Out) inventory strategies via
 * expiry_date, as well as pharmaceutical compliance workflows via
 * quarantine/recall status transitions.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $product_id
 * @property string      $lot_type
 * @property string      $lot_number
 * @property string|null $serial_number
 * @property string|null $batch_number
 * @property string|null $manufacture_date
 * @property string|null $expiry_date
 * @property string|null $best_before_date
 * @property string      $status
 * @property string|null $supplier_lot
 * @property string|null $origin_country
 * @property array|null  $metadata
 */
final class StockLot extends TenantAwareModel
{
    /** @var string */
    protected $table = 'stock_lots';

    /** @var array<string, string> */
    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date'      => 'date',
        'best_before_date' => 'date',
        'metadata'         => 'array',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /** Valid lot types. */
    public const LOT_TYPES = ['serial', 'lot', 'batch'];

    /** Valid lot statuses. */
    public const STATUSES = ['available', 'reserved', 'quarantined', 'expired', 'consumed', 'recalled'];

    /**
     * Return whether this lot is available for picking.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && !$this->isExpired();
    }

    /**
     * Return whether this lot has passed its expiry date.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}
