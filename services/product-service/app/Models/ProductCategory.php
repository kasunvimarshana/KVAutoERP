<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Product category with support for unlimited nesting via self-reference.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string|null $parent_id
 * @property string      $name
 * @property string      $slug
 * @property string|null $description
 * @property bool        $is_active
 * @property int         $sort_order
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class ProductCategory extends TenantAwareModel
{
    /** @var string */
    protected $table = 'product_categories';

    /** @var array<string, string> */
    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Parent category (null for top-level categories).
     *
     * @return BelongsTo<ProductCategory, ProductCategory>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Immediate child categories.
     *
     * @return HasMany<ProductCategory>
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    /**
     * Products belonging to this category.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
