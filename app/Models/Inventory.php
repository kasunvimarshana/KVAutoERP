<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Service B: Inventory Service
 * Represents an inventory record managed by the Inventory Service.
 * Created/updated/deleted in response to ProductCreated/Updated/Deleted events from Service A.
 */
class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_name',
        'quantity',
        'warehouse_location',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Cross-service relationship back to the product in Service A.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
