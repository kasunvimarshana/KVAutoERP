<?php

declare(strict_types=1);

namespace Modules\Barcode\Domain\Entities;

class BarcodePrintJob
{
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_CANCELLED  = 'cancelled';

    public function __construct(
        private readonly int $id,
        private readonly int $tenantId,
        private readonly ?int $labelTemplateId,
        private readonly ?int $barcodeId,
        private string $status,
        private readonly ?string $printerId,
        private readonly int $copies,
        private ?\DateTimeInterface $printedAt,
        private ?string $errorMessage,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }

    public function getLabelTemplateId(): ?int
    {
        return $this->labelTemplateId;
    }

    public function getBarcodeId(): ?int
    {
        return $this->barcodeId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPrinterId(): ?string
    {
        return $this->printerId;
    }

    public function getCopies(): int
    {
        return $this->copies;
    }

    public function getPrintedAt(): ?\DateTimeInterface
    {
        return $this->printedAt;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function markCompleted(): void
    {
        $this->status    = self::STATUS_COMPLETED;
        $this->printedAt = new \DateTimeImmutable();
    }

    public function markFailed(string $error): void
    {
        $this->status       = self::STATUS_FAILED;
        $this->errorMessage = $error;
    }
}
