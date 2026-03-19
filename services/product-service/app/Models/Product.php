<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Product domain entity.
 *
 * Supports all product types (physical, consumable, service, digital,
 * bundle, composite, variant-based) with full traceability, multi-UOM,
 * and multi-currency pricing capabilities.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $organization_id
 * @property string|null $branch_id
 * @property string      $sku
 * @property string|null $barcode
 * @property string|null $barcode_type
 * @property string      $name
 * @property string      $slug
 * @property string|null $description
 * @property string      $type
 * @property string      $status
 * @property string|null $category_id
 * @property string|null $base_uom_id
 * @property string|null $buying_uom_id
 * @property string|null $selling_uom_id
 * @property string      $cost_method
 * @property bool        $is_serialized
 * @property bool        $is_lot_tracked
 * @property bool        $is_batch_tracked
 * @property bool        $has_expiry
 * @property float|null  $weight
 * @property string|null $weight_unit
 * @property array|null  $dimensions
 * @property array|null  $images
 * @property array|null  $metadata
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 */
final class Product extends TenantAwareModel
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'products';

    /** @var array<string, string> */
    protected $casts = [
        'is_serialized'   => 'boolean',
        'is_lot_tracked'  => 'boolean',
        'is_batch_tracked' => 'boolean',
        'has_expiry'      => 'boolean',
        'weight'          => 'float',
        'dimensions'      => 'array',
        'images'          => 'array',
        'metadata'        => 'array',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    /** Valid product types. */
    public const TYPES = [
        'physical', 'consumable', 'service', 'digital',
        'bundle', 'composite', 'variant',
    ];

    /** Valid product statuses. */
    public const STATUSES = ['active', 'inactive', 'discontinued'];

    /** Valid barcode types. */
    public const BARCODE_TYPES = ['EAN13', 'EAN8', 'UPC', 'QR', 'CODE128', 'GS1'];

    /** Valid cost/valuation methods. */
    public const COST_METHODS = ['fifo', 'lifo', 'weighted_average'];

    /**
     * The owning category.
     *
     * @return BelongsTo<ProductCategory, Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Base unit of measure.
     *
     * @return BelongsTo<UnitOfMeasure, Product>
     */
    public function baseUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_uom_id');
    }

    /**
     * Buying unit of measure.
     *
     * @return BelongsTo<UnitOfMeasure, Product>
     */
    public function buyingUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'buying_uom_id');
    }

    /**
     * Selling unit of measure.
     *
     * @return BelongsTo<UnitOfMeasure, Product>
     */
    public function sellingUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'selling_uom_id');
    }

    /**
     * Product prices (multi-currency, multi-tier).
     *
     * @return HasMany<ProductPrice>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'product_id');
    }

    /**
     * Product variants.
     *
     * @return HasMany<ProductVariant>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    /**
     * Product images/assets.
     *
     * @return HasMany<ProductImage>
     */
    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * Return whether this product supports serial/lot/batch traceability.
     *
     * @return bool
     */
    public function isTraceable(): bool
    {
        return $this->is_serialized || $this->is_lot_tracked || $this->is_batch_tracked;
    }

    /**
     * Return whether this product is a bundle or composite (has sub-products).
     *
     * @return bool
     */
    public function isComposite(): bool
    {
        return in_array($this->type, ['bundle', 'composite'], true);
    }
}
