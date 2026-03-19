<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Concerns\GeneratesUuid;

/**
 * StockTransferLine — one product line within a StockTransfer.
 *
 * @property string $id
 * @property string $transfer_id
 * @property string $tenant_id
 * @property string $product_id
 * @property string|null $lot_id
 * @property string $qty_requested
 * @property string $qty_dispatched
 * @property string $qty_received
 * @property string|null $uom_id
 * @property string $unit_cost
 * @property string|null $notes
 */
final class StockTransferLine extends Model
{
    use GeneratesUuid;

    /** @var string */
    protected $table = 'stock_transfer_lines';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'qty_requested'  => 'string',
        'qty_dispatched' => 'string',
        'qty_received'   => 'string',
        'unit_cost'      => 'string',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /**
     * The parent transfer record.
     *
     * @return BelongsTo<StockTransfer, StockTransferLine>
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
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
