<?php

declare(strict_types=1);

namespace App\Domain\Order\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Order Entity.
 */
class Order extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'status',            // pending|confirmed|processing|fulfilled|cancelled|failed
        'saga_status',       // pending|running|completed|compensating|failed
        'saga_transaction_id',
        'subtotal',
        'tax_amount',
        'total_amount',
        'currency',
        'notes',
        'metadata',
        'fulfilled_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:4',
        'tax_amount'   => 'decimal:4',
        'total_amount' => 'decimal:4',
        'metadata'     => 'array',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function sagaLog(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SagaLog::class, 'order_id');
    }

    // =========================================================================
    // State Transitions
    // =========================================================================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeConfirmed(): bool
    {
        return in_array($this->status, ['pending'], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing'], true);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
}
