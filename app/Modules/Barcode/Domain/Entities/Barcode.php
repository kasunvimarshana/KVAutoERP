<?php

declare(strict_types=1);

namespace Modules\Barcode\Domain\Entities;

class Barcode
{
    public const VALID_SYMBOLOGIES = [
        'ean13', 'ean8', 'upc_a', 'code128', 'code93', 'code39', 'itf',
        'qr_code', 'data_matrix', 'pdf417', 'aztec',
        'interleaved2of5', 'codabar', 'msi', 'plessey',
    ];

    public function __construct(
        private readonly int $id,
        private readonly int $tenantId,
        private readonly string $symbology,
        private readonly string $data,
        private readonly ?string $checkDigit,
        private readonly string $encodedData,
        private readonly \DateTimeInterface $generatedAt,
        private readonly array $metadata,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }

    public function getSymbology(): string
    {
        return $this->symbology;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getCheckDigit(): ?string
    {
        return $this->checkDigit;
    }

    public function getEncodedData(): string
    {
        return $this->encodedData;
    }

    public function getGeneratedAt(): \DateTimeInterface
    {
        return $this->generatedAt;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function hasCheckDigit(): bool
    {
        return $this->checkDigit !== null && $this->checkDigit !== '';
    }
}
