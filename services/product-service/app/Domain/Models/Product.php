<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'category_id', 'name', 'code', 'slug', 'description',
        'short_description', 'price', 'cost_price', 'compare_price', 'sku',
        'barcode', 'unit', 'weight', 'dimensions', 'images', 'attributes',
        'tags', 'is_active', 'is_featured', 'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'cost_price' => 'decimal:4',
        'compare_price' => 'decimal:4',
        'weight' => 'decimal:4',
        'dimensions' => 'array',
        'images' => 'array',
        'attributes' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
