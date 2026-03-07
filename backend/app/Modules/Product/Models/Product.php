<?php

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'category',
        'tenant_id',
        'attributes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    public function inventory()
    {
        return $this->hasOne(\App\Modules\Inventory\Models\Inventory::class);
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Modules\Tenant\Models\Tenant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(\App\Modules\Order\Models\OrderItem::class);
    }
}
