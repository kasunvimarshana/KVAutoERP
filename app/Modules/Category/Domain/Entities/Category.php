<?php
declare(strict_types=1);
namespace Modules\Category\Domain\Entities;

use DateTimeImmutable;
use Illuminate\Support\Collection;

class Category
{
    private ?int $id;
    private int $tenantId;
    private string $name;
    private string $slug;
    private ?int $parentId;
    private string $status;
    private ?string $description;
    private int $depth;
    private string $path;
    private ?array $attributes;
    private ?array $metadata;
    private ?CategoryImage $image = null;
    private Collection $children;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $tenantId,
        string $name,
        string $slug,
        ?string $description = null,
        ?int $parentId = null,
        int $depth = 0,
        string $path = '',
        string $status = 'active',
        ?array $attributes = null,
        ?array $metadata = null,
        ?int $id = null,
    ) {
        $this->tenantId    = $tenantId;
        $this->name        = $name;
        $this->slug        = $slug;
        $this->description = $description;
        $this->parentId    = $parentId;
        $this->depth       = $depth;
        $this->path        = $path;
        $this->status      = $status;
        $this->attributes  = $attributes;
        $this->metadata    = $metadata;
        $this->id          = $id;
        $this->children    = new Collection();
        $this->createdAt   = new DateTimeImmutable();
        $this->updatedAt   = new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getStatus(): string { return $this->status; }
    public function getDescription(): ?string { return $this->description; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getDepth(): int { return $this->depth; }
    public function getPath(): string { return $this->path; }
    public function getAttributes(): ?array { return $this->attributes; }
    public function getMetadata(): ?array { return $this->metadata; }
    public function getImage(): ?CategoryImage { return $this->image; }
    public function getChildren(): Collection { return $this->children; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function isRoot(): bool { return $this->parentId === null; }
    public function isActive(): bool { return $this->status === 'active'; }
    public function hasChildren(): bool { return $this->children->isNotEmpty(); }

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

    public function setImage(?CategoryImage $image): void
    {
        $this->image = $image;
    }

    public function addChild(self $child): void
    {
        $this->children->push($child);
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function updateDetails(
        string $name,
        string $slug,
        ?string $description,
        ?int $parentId,
        string $path,
        int $depth,
        ?array $attributes,
        ?array $metadata,
    ): void {
        $this->name        = $name;
        $this->slug        = $slug;
        $this->description = $description;
        $this->parentId    = $parentId;
        $this->path        = $path;
        $this->depth       = $depth;
        $this->attributes  = $attributes;
        $this->metadata    = $metadata;
        $this->updatedAt   = new DateTimeImmutable();
    }

    /** @deprecated use updateDetails */
    public function update(array $data): void
    {
        if (isset($data['name'])) $this->name = $data['name'];
        if (isset($data['slug'])) $this->slug = $data['slug'];
        $this->updatedAt = new DateTimeImmutable();
    }
}
