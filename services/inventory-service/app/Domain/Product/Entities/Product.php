<?php

declare(strict_types=1);

namespace App\Domain\Product\Entities;

use App\Domain\Category\Entities\Category;
use App\Domain\Inventory\Entities\InventoryItem;
use App\Domain\Product\Events\ProductCreated;
use App\Domain\Product\Events\ProductDiscontinued;
use App\Domain\Product\Events\ProductUpdated;
use App\Domain\Product\ValueObjects\Sku;
use App\Domain\Supplier\Entities\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'products';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'short_description',
        'barcode',
        'brand',
        'unit_of_measure',
        'status',
        'type',
        'weight',
        'dimensions',
        'images',
        'tags',
        'attributes',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'dimensions'  => 'array',
        'images'      => 'array',
        'tags'        => 'array',
        'attributes'  => 'array',
        'metadata'    => 'array',
        'weight'      => 'float',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    protected $attributes = [
        'status'     => 'active',
        'type'       => 'physical',
        'images'     => '[]',
        'tags'       => '[]',
        'attributes' => '{}',
        'metadata'   => '{}',
    ];

    protected $dispatchesEvents = [
        'created' => ProductCreated::class,
        'updated' => ProductUpdated::class,
    ];

    // Relationships

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(InventoryItem::class, 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id')->orderBy('sort_order');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByCategory(Builder $query, string $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('sku', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('brand', 'LIKE', "%{$term}%")
              ->orWhere('barcode', 'LIKE', "%{$term}%");
        });
    }

    // Domain Methods

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    public function isDiscontinued(): bool
    {
        return $this->status === 'discontinued';
    }

    public function discontinue(): void
    {
        $this->status = 'discontinued';
        $this->save();
        event(new ProductDiscontinued($this));
    }

    public function generateSku(string $prefix = ''): string
    {
        $prefix = $prefix ?: strtoupper(substr($this->name ?? 'PRD', 0, 3));
        $sku = Sku::generate($prefix);
        $this->sku = (string) $sku;
        return $this->sku;
    }

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $tag = strtolower(trim($tag));

        if (!in_array($tag, $tags, true)) {
            $tags[] = $tag;
            $this->tags = $tags;
        }
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $this->tags = array_values(array_filter($tags, fn ($t) => $t !== strtolower(trim($tag))));
    }

    public function getDimensionsObject(): ?\App\Domain\Product\ValueObjects\Dimensions
    {
        if (empty($this->dimensions)) {
            return null;
        }

        return \App\Domain\Product\ValueObjects\Dimensions::fromArray($this->dimensions);
    }
}
