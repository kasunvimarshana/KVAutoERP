<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Product variant entity (colour × size, etc.).
 *
 * Variant attributes are stored as a JSON object enabling arbitrary
 * attribute combinations without schema changes.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $product_id
 * @property string      $sku
 * @property string      $name
 * @property array       $attributes   e.g. {"color": "red", "size": "M"}
 * @property bool        $is_active
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class ProductVariant extends TenantAwareModel
{
    /** @var string */
    protected $table = 'product_variants';

    /** @var array<string, string> */
    protected $casts = [
        'attributes' => 'array',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Parent product.
     *
     * @return BelongsTo<Product, ProductVariant>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
