<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Domain\Entities;

class OrganizationUnitAttachment {
    private ?int $id;
    private int $tenantId;
    private int $organizationUnitId;
    private string $uuid;
    private string $name;
    private string $filePath;
    private string $mimeType;
    private int $size;
    private ?string $type;
    private ?array $metadata;

    public function __construct(
        int $tenantId,
        int $organizationUnitId,
        string $uuid,
        string $name,
        string $filePath,
        string $mimeType,
        int $size,
        ?string $type = null,
        ?array $metadata = null,
        ?int $id = null
    ) {
        $this->tenantId = $tenantId;
        $this->organizationUnitId = $organizationUnitId;
        $this->uuid = $uuid;
        $this->name = $name;
        $this->filePath = $filePath;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->type = $type;
        $this->metadata = $metadata;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getOrganizationUnitId(): int { return $this->organizationUnitId; }
    public function getUuid(): string { return $this->uuid; }
    public function getName(): string { return $this->name; }
    public function getFilePath(): string { return $this->filePath; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getSize(): int { return $this->size; }
    public function getType(): ?string { return $this->type; }
    public function getMetadata(): ?array { return $this->metadata; }

    public function updateDetails(?string $type, ?array $metadata): void {
        $this->type = $type;
        $this->metadata = $metadata;
    }

    public function updateFile(string $name, string $filePath, string $mimeType, int $size): void {
        $this->name = $name;
        $this->filePath = $filePath;
        $this->mimeType = $mimeType;
        $this->size = $size;
    }
}
