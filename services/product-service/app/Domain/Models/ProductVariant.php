<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id', 'name', 'sku', 'barcode', 'price', 'cost_price',
        'weight', 'attributes', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'cost_price' => 'decimal:4',
        'weight' => 'decimal:4',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
