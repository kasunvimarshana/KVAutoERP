<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class Attachment
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $attachableType,
        private readonly int $attachableId,
        private readonly string $filename,
        private readonly string $originalName,
        private readonly string $mimeType,
        private readonly int $size,
        private readonly string $disk,
        private readonly string $path,
        private readonly array $metadata,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function getAttachableType(): string
    {
        return $this->attachableType;
    }

    public function getAttachableId(): int
    {
        return $this->attachableId;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUrl(): string
    {
        return $this->path;
    }
}
