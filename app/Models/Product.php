<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Service A: Product Service
 * Represents a product managed by the Product Service.
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sku',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * A product may have inventory records in Service B.
     * This cross-service relationship is resolved locally for reporting purposes.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_id');
    }
}
