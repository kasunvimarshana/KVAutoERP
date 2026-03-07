<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'currency',
        'shipping_address',
        'billing_address',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'tax'              => 'decimal:2',
        'discount'         => 'decimal:2',
        'total'            => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address'  => 'array',
        'metadata'         => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(Str::random(4)) . '-' . now()->format('ymd') . '-' . random_int(1000, 9999);
    }

    public function scopeTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function saga(): HasMany
    {
        return $this->hasMany(OrderSaga::class);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }
}
