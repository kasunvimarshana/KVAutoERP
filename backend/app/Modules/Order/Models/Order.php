<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    const STATUS_PENDING    = 'pending';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED    = 'shipped';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_CANCELLED  = 'cancelled';

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
        'notes',
        'shipping_address',
        'billing_address',
        'metadata',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'tax'              => 'decimal:2',
        'discount'         => 'decimal:2',
        'total'            => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address'  => 'array',
        'metadata'         => 'array',
        'completed_at'     => 'datetime',
        'cancelled_at'     => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public static function generateOrderNumber(string $tenantId): string
    {
        $prefix    = strtoupper(substr($tenantId, 0, 4));
        $timestamp = now()->format('YmdHis');
        $random    = strtoupper(substr(uniqid(), -4));
        return "ORD-{$prefix}-{$timestamp}-{$random}";
    }
}
