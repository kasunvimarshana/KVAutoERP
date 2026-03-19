<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id', // Multi-location pricing
        'currency_code', // Multi-currency pricing
        'price',
        'cost',
        'min_quantity', // Tiered pricing
        'max_quantity',
        'is_default',
        'effective_date',
        'expiry_date',
        'rule_id', // Reference to rule-based pricing engine
    ];

    protected $casts = [
        'price' => 'decimal:10',
        'cost' => 'decimal:10',
        'min_quantity' => 'decimal:10',
        'max_quantity' => 'decimal:10',
        'is_default' => 'boolean',
        'effective_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
