<?php
declare(strict_types=1);
namespace Modules\Product\Domain\Entities;

use DateTimeImmutable;
use Illuminate\Support\Collection;
use Modules\Core\Domain\ValueObjects\Money;
use Modules\Core\Domain\ValueObjects\Sku;
use Modules\Product\Domain\ValueObjects\ProductAttribute;
use Modules\Product\Domain\ValueObjects\ProductType;
use Modules\Product\Domain\ValueObjects\UnitOfMeasure;

class Product
{
    private ?int $id;
    private int $tenantId;
    private Sku $sku;
    private string $name;
    private Money $price;
    private ?string $description;
    private ?string $category;
    private string $status;
    private ProductType $type;
    private array $unitsOfMeasure;
    private array $productAttributes;
    private ?array $attributes;
    private ?array $metadata;
    private Collection $images;
    private Collection $variations;
    private Collection $comboItems;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $tenantId,
        Sku $sku,
        string $name,
        Money $price,
        ?string $description = null,
        ?string $category = null,
        string $status = 'active',
        string $type = 'physical',
        array $unitsOfMeasure = [],
        array $productAttributes = [],
        ?array $attributes = null,
        ?array $metadata = null,
        ?int $id = null,
    ) {
        $this->tenantId          = $tenantId;
        $this->sku               = $sku;
        $this->name              = $name;
        $this->price             = $price;
        $this->description       = $description;
        $this->category          = $category;
        $this->status            = $status;
        $this->type              = new ProductType($type);
        $this->unitsOfMeasure    = $unitsOfMeasure;
        $this->productAttributes = $productAttributes;
        $this->attributes        = $attributes;
        $this->metadata          = $metadata;
        $this->id                = $id;
        $this->images            = new Collection();
        $this->variations        = new Collection();
        $this->comboItems        = new Collection();
        $this->createdAt         = new DateTimeImmutable();
        $this->updatedAt         = new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getSku(): Sku { return $this->sku; }
    public function getName(): string { return $this->name; }
    public function getPrice(): Money { return $this->price; }
    public function getDescription(): ?string { return $this->description; }
    public function getCategory(): ?string { return $this->category; }
    public function getStatus(): string { return $this->status; }
    public function getType(): ProductType { return $this->type; }
    public function getUnitsOfMeasure(): array { return $this->unitsOfMeasure; }
    public function getProductAttributes(): array { return $this->productAttributes; }
    public function getAttributes(): ?array { return $this->attributes; }
    public function getMetadata(): ?array { return $this->metadata; }
    public function getImages(): Collection { return $this->images; }
    public function getVariations(): Collection { return $this->variations; }
    public function getComboItems(): Collection { return $this->comboItems; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function isActive(): bool { return $this->status === 'active'; }

    public function activate(): void
    {
        $this->status    = 'active';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->status    = 'inactive';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getBuyingUnit(): ?UnitOfMeasure
    {
        foreach ($this->unitsOfMeasure as $uom) {
            if ($uom instanceof UnitOfMeasure && $uom->isBuying()) return $uom;
        }
        return null;
    }

    public function getSellingUnit(): ?UnitOfMeasure
    {
        foreach ($this->unitsOfMeasure as $uom) {
            if ($uom instanceof UnitOfMeasure && $uom->isSelling()) return $uom;
        }
        return null;
    }

    public function getInventoryUnit(): ?UnitOfMeasure
    {
        foreach ($this->unitsOfMeasure as $uom) {
            if ($uom instanceof UnitOfMeasure && $uom->isInventory()) return $uom;
        }
        return null;
    }

    public function getPrimaryImage(): ?ProductImage
    {
        foreach ($this->images as $image) {
            if ($image instanceof ProductImage && $image->isPrimary()) return $image;
        }
        return null;
    }

    public function addImage(ProductImage $image): void { $this->images->push($image); }
    public function addVariation(ProductVariation $variation): void { $this->variations->push($variation); }
    public function addComboItem(ComboItem $item): void { $this->comboItems->push($item); }

    public function setVariations(Collection $variations): void { $this->variations = $variations; }
    public function setComboItems(Collection $items): void { $this->comboItems = $items; }

    public function updateDetails(
        string $name,
        Money $price,
        ?string $description,
        ?string $category,
        ?array $attributes,
        ?array $metadata,
        ?string $type = null,
        ?array $unitsOfMeasure = null,
        ?array $productAttributes = null,
    ): void {
        $this->name        = $name;
        $this->price       = $price;
        $this->description = $description;
        $this->category    = $category;
        $this->attributes  = $attributes;
        $this->metadata    = $metadata;
        if ($type !== null) {
            $this->type = new ProductType($type);
        }
        if ($unitsOfMeasure !== null) {
            $this->unitsOfMeasure = $unitsOfMeasure;
        }
        if ($productAttributes !== null) {
            $this->productAttributes = $productAttributes;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function draft(): void
    {
        $this->status    = 'draft';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isDraft(): bool { return $this->status === 'draft'; }
}
