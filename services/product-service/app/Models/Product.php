<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'description',
        'category',
        'price',
        'cost',
        'stock_quantity',
        'min_stock_level',
        'unit',
        'status',
        'metadata',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'cost'            => 'decimal:2',
        'stock_quantity'  => 'integer',
        'min_stock_level' => 'integer',
        'metadata'        => 'array',
    ];

    public function scopeTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }
}
