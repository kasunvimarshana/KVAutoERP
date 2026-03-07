<?php

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'description',
        'category',
        'brand',
        'unit',
        'price',
        'cost',
        'is_active',
        'attributes',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'cost'       => 'decimal:2',
        'is_active'  => 'boolean',
        'attributes' => 'array',
    ];

    public function inventory()
    {
        return $this->hasMany(\App\Modules\Inventory\Models\Inventory::class, 'product_id');
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
