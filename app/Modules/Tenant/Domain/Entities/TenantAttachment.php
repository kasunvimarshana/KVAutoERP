<?php
declare(strict_types=1);
namespace Modules\Tenant\Domain\Entities;

class TenantAttachment {
    private ?int $id;
    private int $tenantId;
    private string $uuid;
    private string $name;
    private string $filePath;
    private string $mimeType;
    private int $size;
    private ?string $type;
    private ?array $metadata;

    public function __construct(
        int $tenantId,
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
    public function getUuid(): string { return $this->uuid; }
    public function getName(): string { return $this->name; }
    public function getFilePath(): string { return $this->filePath; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getSize(): int { return $this->size; }
    public function getType(): ?string { return $this->type; }
    public function getMetadata(): ?array { return $this->metadata; }
}
