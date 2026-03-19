<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Multi-currency, multi-location product price.
 *
 * Prices are stored to 4 decimal places and calculated using BCMath
 * to maintain financial precision across all operations.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $product_id
 * @property string      $currency_code    ISO 4217 (e.g. USD, EUR, GBP)
 * @property string      $price_type       base | buying | selling | tier
 * @property string|null $tier_min_qty     Minimum quantity for tier pricing (BCMath string)
 * @property string      $price            Decimal stored as string for BCMath precision
 * @property string|null $valid_from
 * @property string|null $valid_to
 * @property string|null $location_id
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class ProductPrice extends TenantAwareModel
{
    /** @var string */
    protected $table = 'product_prices';

    /** @var array<string, string> */
    protected $casts = [
        'valid_from' => 'date',
        'valid_to'   => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Valid price types. */
    public const PRICE_TYPES = ['base', 'buying', 'selling', 'tier'];

    /**
     * The product this price belongs to.
     *
     * @return BelongsTo<Product, ProductPrice>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
