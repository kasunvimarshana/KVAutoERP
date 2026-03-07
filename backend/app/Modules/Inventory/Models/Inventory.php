<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    const STATUS_IN_STOCK    = 'in_stock';
    const STATUS_LOW_STOCK   = 'low_stock';
    const STATUS_OUT_OF_STOCK = 'out_of_stock';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'warehouse_location',
        'quantity',
        'reserved_quantity',
        'minimum_quantity',
        'maximum_quantity',
        'status',
        'last_restocked_at',
        'metadata',
    ];

    protected $casts = [
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
        'minimum_quantity'  => 'integer',
        'maximum_quantity'  => 'integer',
        'metadata'          => 'array',
        'last_restocked_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Modules\Product\Models\Product::class, 'product_id');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= minimum_quantity AND quantity > 0');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    public function updateStatus(): void
    {
        if ($this->quantity <= 0) {
            $this->status = self::STATUS_OUT_OF_STOCK;
        } elseif ($this->quantity <= $this->minimum_quantity) {
            $this->status = self::STATUS_LOW_STOCK;
        } else {
            $this->status = self::STATUS_IN_STOCK;
        }
    }
}
