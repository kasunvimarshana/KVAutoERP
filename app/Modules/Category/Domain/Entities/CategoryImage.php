<?php
declare(strict_types=1);
namespace Modules\Category\Domain\Entities;

class CategoryImage
{
    private ?int $id;
    private int $tenantId;
    private int $categoryId;
    private string $uuid;
    private string $name;
    private string $filePath;
    private string $mimeType;
    private int $size;
    private ?array $metadata;

    public function __construct(
        int $tenantId,
        int $categoryId,
        string $uuid,
        string $name,
        string $filePath,
        string $mimeType,
        int $size,
        ?array $metadata = null,
        ?int $id = null,
    ) {
        $this->tenantId   = $tenantId;
        $this->categoryId = $categoryId;
        $this->uuid       = $uuid;
        $this->name       = $name;
        $this->filePath   = $filePath;
        $this->mimeType   = $mimeType;
        $this->size       = $size;
        $this->metadata   = $metadata;
        $this->id         = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getCategoryId(): int { return $this->categoryId; }
    public function getUuid(): string { return $this->uuid; }
    public function getName(): string { return $this->name; }
    public function getFilePath(): string { return $this->filePath; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getSize(): int { return $this->size; }
    public function getMetadata(): ?array { return $this->metadata; }
}
