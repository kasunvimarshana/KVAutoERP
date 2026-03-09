<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Models;

use App\Core\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Order model.
 *
 * Represents a customer order managed via the Saga orchestration pattern.
 *
 * @property int         $id
 * @property int         $tenant_id
 * @property int         $customer_id
 * @property string      $status       pending|confirmed|processing|shipped|delivered|cancelled|failed
 * @property string      $saga_status  pending|completed|compensating|compensated|failed
 * @property string|null $saga_correlation_id   UUID linking all Saga messages
 * @property float       $total_amount
 * @property array|null  $metadata
 */
class Order extends Model
{
    use HasFactory;
    use HasTenant;
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'status',
        'saga_status',
        'saga_correlation_id',
        'total_amount',
        'metadata',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'metadata'     => 'array',
    ];

    // Status constants
    public const STATUS_PENDING    = 'pending';
    public const STATUS_CONFIRMED  = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED    = 'shipped';
    public const STATUS_DELIVERED  = 'delivered';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_FAILED     = 'failed';

    // Saga status constants
    public const SAGA_PENDING      = 'pending';
    public const SAGA_COMPLETED    = 'completed';
    public const SAGA_COMPENSATING = 'compensating';
    public const SAGA_COMPENSATED  = 'compensated';
    public const SAGA_FAILED       = 'failed';

    // -------------------------------------------------------------------------
    //  Relations
    // -------------------------------------------------------------------------

    /** @return HasMany<OrderItem> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // -------------------------------------------------------------------------
    //  Helpers
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }
}
