<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Product image / digital asset.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $product_id
 * @property string      $url
 * @property string|null $alt_text
 * @property int         $sort_order
 * @property bool        $is_primary
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class ProductImage extends TenantAwareModel
{
    /** @var string */
    protected $table = 'product_images';

    /** @var array<string, string> */
    protected $casts = [
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Parent product.
     *
     * @return BelongsTo<Product, ProductImage>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
