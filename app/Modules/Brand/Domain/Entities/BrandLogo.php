<?php
declare(strict_types=1);
namespace Modules\Brand\Domain\Entities;

class BrandLogo
{
    private ?int $id;
    private int $tenantId;
    private int $brandId;
    private string $uuid;
    private string $name;
    private string $filePath;
    private string $mimeType;
    private int $size;
    private ?array $metadata;

    public function __construct(
        int $tenantId,
        int $brandId,
        string $uuid,
        string $name,
        string $filePath,
        string $mimeType,
        int $size,
        ?array $metadata = null,
        ?int $id = null,
    ) {
        $this->tenantId  = $tenantId;
        $this->brandId   = $brandId;
        $this->uuid      = $uuid;
        $this->name      = $name;
        $this->filePath  = $filePath;
        $this->mimeType  = $mimeType;
        $this->size      = $size;
        $this->metadata  = $metadata;
        $this->id        = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getBrandId(): int { return $this->brandId; }
    public function getUuid(): string { return $this->uuid; }
    public function getName(): string { return $this->name; }
    public function getFilePath(): string { return $this->filePath; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getSize(): int { return $this->size; }
    public function getMetadata(): ?array { return $this->metadata; }
}
