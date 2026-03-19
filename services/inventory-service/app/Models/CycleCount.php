<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * CycleCount — a physical inventory counting session for a warehouse.
 *
 * When confirmed, variances between system_qty and counted_qty in the
 * CycleCountLines are written as adjustment entries to the StockLedger.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $warehouse_id
 * @property string|null $count_number
 * @property string      $status
 * @property string      $count_type
 * @property \Carbon\Carbon|null $scheduled_at
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon|null $confirmed_at
 * @property string|null $notes
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $confirmed_by
 */
final class CycleCount extends TenantAwareModel
{
    /** @var string */
    protected $table = 'cycle_counts';

    /** @var array<string, string> */
    protected $casts = [
        'scheduled_at'  => 'datetime',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
        'confirmed_at'  => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /** Valid cycle count statuses. */
    public const STATUSES = ['draft', 'in_progress', 'pending_approval', 'confirmed', 'cancelled'];

    /** Valid count types. */
    public const COUNT_TYPES = ['full', 'partial', 'abc_class', 'zone', 'random'];

    /**
     * The warehouse being counted.
     *
     * @return BelongsTo<Warehouse, CycleCount>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * The line items (one per product/bin/lot).
     *
     * @return HasMany<CycleCountLine>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(CycleCountLine::class, 'cycle_count_id');
    }

    /**
     * Return whether this count can be confirmed (all lines counted).
     *
     * @return bool
     */
    public function canConfirm(): bool
    {
        return in_array($this->status, ['in_progress', 'pending_approval'], true);
    }
}
