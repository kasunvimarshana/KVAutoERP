<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Concerns\GeneratesUuid;

/**
 * CycleCountLine — one product/bin/lot row within a CycleCount.
 *
 * @property string      $id
 * @property string      $cycle_count_id
 * @property string      $tenant_id
 * @property string      $product_id
 * @property string|null $bin_id
 * @property string|null $lot_id
 * @property string      $system_qty    BCMath string, 4dp
 * @property string|null $counted_qty   BCMath string, 4dp
 * @property string|null $variance_qty  Computed: counted - system
 * @property string      $unit_cost
 * @property string|null $uom_id
 * @property string      $count_status
 * @property string|null $notes
 * @property string|null $counted_by
 * @property \Carbon\Carbon|null $counted_at
 */
final class CycleCountLine extends Model
{
    use GeneratesUuid;

    /** @var string */
    protected $table = 'cycle_count_lines';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'system_qty'   => 'string',
        'counted_qty'  => 'string',
        'variance_qty' => 'string',
        'unit_cost'    => 'string',
        'counted_at'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /** Valid line statuses. */
    public const STATUSES = ['pending', 'counted', 'approved', 'adjusted'];

    /**
     * Parent cycle count.
     *
     * @return BelongsTo<CycleCount, CycleCountLine>
     */
    public function cycleCount(): BelongsTo
    {
        return $this->belongsTo(CycleCount::class, 'cycle_count_id');
    }

    /**
     * Return the actual variance (may be null if not yet counted).
     *
     * @return string|null
     */
    public function computedVariance(): ?string
    {
        if ($this->counted_qty === null) {
            return null;
        }

        return bcsub($this->counted_qty, $this->system_qty, 4);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(static function (self $model): void {
            if (empty($model->id)) {
                $model->id = static::generateUuidV4();
            }
        });
    }
}
