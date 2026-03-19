<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Concerns\GeneratesUuid;

/**
 * WarehouseBin — a named storage location within a warehouse.
 *
 * Not tenant-aware in isolation (tenant isolation is inherited from
 * the parent Warehouse); queries always join through warehouse.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $warehouse_id
 * @property string      $code
 * @property string|null $name
 * @property string|null $zone
 * @property string|null $aisle
 * @property string|null $rack
 * @property string|null $shelf
 * @property string|null $position
 * @property string      $type
 * @property string      $status
 * @property string|null $capacity
 * @property array|null  $metadata
 */
final class WarehouseBin extends Model
{
    use GeneratesUuid;

    /** @var string */
    protected $table = 'warehouse_bins';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'capacity'   => 'string',
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Valid bin types. */
    public const TYPES = ['standard', 'receiving', 'dispatch', 'quarantine', 'damaged'];

    /** Valid bin statuses. */
    public const STATUSES = ['active', 'inactive', 'full'];

    /**
     * The warehouse this bin belongs to.
     *
     * @return BelongsTo<Warehouse, WarehouseBin>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
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
