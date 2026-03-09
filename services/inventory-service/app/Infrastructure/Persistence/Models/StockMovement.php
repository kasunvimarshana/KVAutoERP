<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Inventory\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for the stock_movements table.
 *
 * @property string             $id
 * @property string             $tenant_id
 * @property string             $product_id
 * @property StockMovementType  $type
 * @property int                $quantity
 * @property string             $reference
 * @property string             $reason
 * @property int                $previous_quantity
 * @property int                $new_quantity
 * @property string             $performed_by
 * @property \Carbon\Carbon     $created_at
 */
class StockMovement extends Model
{
    use HasUuids;

    protected $table = 'stock_movements';

    /** StockMovements are append-only — no updates. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'tenant_id',
        'product_id',
        'type',
        'quantity',
        'reference',
        'reason',
        'previous_quantity',
        'new_quantity',
        'performed_by',
    ];

    protected $casts = [
        'type'              => StockMovementType::class,
        'quantity'          => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity'      => 'integer',
    ];

    protected $attributes = [
        'reference'    => '',
        'reason'       => '',
        'performed_by' => 'system',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
