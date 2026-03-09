<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Models;

use App\Core\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Product model.
 *
 * Represents a product/SKU in the inventory for a specific tenant.
 *
 * @property int    $id
 * @property int    $tenant_id
 * @property string $name
 * @property string $sku
 * @property string $description
 * @property float  $price
 * @property int    $quantity
 * @property int    $reserved_quantity  Quantity locked by pending orders (Saga)
 * @property string $status             active|inactive|discontinued
 * @property string $category
 * @property array  $metadata
 */
class Product extends Model
{
    use HasFactory;
    use HasTenant;
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'description',
        'price',
        'quantity',
        'reserved_quantity',
        'status',
        'category',
        'metadata',
    ];

    protected $casts = [
        'price'             => 'decimal:2',
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
        'metadata'          => 'array',
    ];

    // -------------------------------------------------------------------------
    //  Domain helpers
    // -------------------------------------------------------------------------

    /**
     * Available quantity = total − reserved.
     */
    public function availableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Returns true if stock can satisfy the requested amount.
     */
    public function hasStock(int $amount): bool
    {
        return $this->availableQuantity() >= $amount;
    }

    /**
     * Reserve stock for a pending Saga transaction.
     *
     * @throws \RuntimeException when insufficient stock
     */
    public function reserve(int $amount): void
    {
        if (! $this->hasStock($amount)) {
            throw new \RuntimeException(
                "Insufficient stock for product [{$this->sku}]. "
                . "Available: {$this->availableQuantity()}, Requested: {$amount}."
            );
        }

        $this->increment('reserved_quantity', $amount);
    }

    /**
     * Release previously reserved stock (e.g. on Saga rollback).
     */
    public function releaseReservation(int $amount): void
    {
        $release = min($amount, $this->reserved_quantity);
        $this->decrement('reserved_quantity', $release);
    }

    /**
     * Deduct reserved stock on order fulfilment.
     */
    public function deductOnFulfilment(int $amount): void
    {
        $this->decrement('quantity', $amount);
        $this->decrement('reserved_quantity', $amount);
    }
}
