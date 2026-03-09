<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'product_variants';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'tenant_id',
        'sku',
        'name',
        'attributes',
        'price',
        'compare_at_price',
        'cost_price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'attributes'      => 'array',
        'price'           => 'float',
        'compare_at_price' => 'float',
        'cost_price'      => 'float',
        'is_active'       => 'boolean',
        'sort_order'      => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    protected $attributes = [
        'is_active'  => true,
        'sort_order' => 0,
        'attributes' => '{}',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->price;
    }

    public function getDiscountPercentage(): float
    {
        if (!$this->hasDiscount()) {
            return 0.0;
        }

        return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100, 2);
    }

    public function getMargin(): float
    {
        if ($this->cost_price === null || $this->price <= 0) {
            return 0.0;
        }

        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }
}
