<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * ReorderRule — defines the replenishment triggers for a product at a warehouse.
 *
 * When on-hand quantity falls to or below reorder_point, a procurement
 * suggestion is generated for reorder_qty units.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $product_id
 * @property string      $warehouse_id
 * @property string      $reorder_point  BCMath string, 4dp
 * @property string      $reorder_qty    BCMath string, 4dp
 * @property string|null $max_qty        BCMath string, 4dp
 * @property string      $safety_stock   BCMath string, 4dp
 * @property string|null $uom_id
 * @property int         $lead_time_days
 * @property bool        $is_active
 * @property string|null $preferred_supplier_id
 */
final class ReorderRule extends TenantAwareModel
{
    /** @var string */
    protected $table = 'reorder_rules';

    /** @var array<string, string> */
    protected $casts = [
        'reorder_point' => 'string',
        'reorder_qty'   => 'string',
        'max_qty'       => 'string',
        'safety_stock'  => 'string',
        'lead_time_days' => 'integer',
        'is_active'     => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * The warehouse this rule applies to.
     *
     * @return BelongsTo<Warehouse, ReorderRule>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Return whether a reorder is needed based on current stock.
     *
     * @param  string  $currentQty  Current on-hand as BCMath string.
     * @return bool
     */
    public function needsReorder(string $currentQty): bool
    {
        return $this->is_active && bccomp($currentQty, $this->reorder_point, 4) <= 0;
    }
}
