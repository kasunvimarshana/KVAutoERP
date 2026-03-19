<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'type', // Physical, Consumable, Service, Digital, Bundle, Composite, Variant-based
        'category_id',
        'base_uom_id',
        'buying_uom_id',
        'selling_uom_id',
        'costing_method', // FIFO, LIFO, Weighted Average
        'valuation_method',
        'is_traceable',
        'traceability_type', // Serial, Batch, Lot
        'is_gs1_compliant',
        'barcode',
        'qr_code',
        'status',
    ];

    protected $casts = [
        'is_traceable' => 'boolean',
        'is_gs1_compliant' => 'boolean',
        'metadata' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function baseUom(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_uom_id');
    }

    public function buyingUom(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'buying_uom_id')->withDefault(function ($uom, $product) {
            return $product->baseUom;
        });
    }

    public function sellingUom(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'selling_uom_id')->withDefault(function ($uom, $product) {
            return $product->baseUom;
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
