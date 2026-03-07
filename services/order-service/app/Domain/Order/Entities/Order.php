<?php

namespace App\Domain\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    use SoftDeletes;

    const STATUS_PENDING    = 'pending';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_FAILED     = 'failed';

    const PAYMENT_STATUS_PENDING  = 'pending';
    const PAYMENT_STATUS_PAID     = 'paid';
    const PAYMENT_STATUS_FAILED   = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $table = 'orders';

    protected $fillable = [
        'tenant_id',
        'order_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'items',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'shipping_address',
        'billing_address',
        'notes',
        'saga_id',
        'metadata',
    ];

    protected $casts = [
        'items'            => 'array',
        'shipping_address' => 'array',
        'billing_address'  => 'array',
        'metadata'         => 'array',
        'subtotal'         => 'decimal:2',
        'tax'              => 'decimal:2',
        'discount'         => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2);
    }

    // -------------------------------------------------------------------------
    // Domain methods
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    public function calculateTotal(): float
    {
        $subtotal = collect($this->items)->sum(function (array $item) {
            return ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1);
        });

        $this->subtotal = $subtotal;
        $this->total    = $subtotal + ($this->tax ?? 0) - ($this->discount ?? 0);

        return (float) $this->total;
    }

    public static function generateOrderNumber(): string
    {
        // UUID-based order number: globally unique, no collision risk in distributed systems
        return 'ORD-' . strtoupper(str_replace('-', '', (string) \Illuminate\Support\Str::uuid()));
    }
}
