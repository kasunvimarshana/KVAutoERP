<?php declare(strict_types=1);
namespace Modules\Barcode\Domain\Entities;
class BarcodePrintJob {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $labelTemplateId,
        private readonly string $barcodeData,
        private readonly string $barcodeType,
        private readonly int $copies,
        private readonly string $status,
        private readonly ?string $errorMessage,
        private readonly ?\DateTimeInterface $processedAt,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getLabelTemplateId(): int { return $this->labelTemplateId; }
    public function getBarcodeData(): string { return $this->barcodeData; }
    public function getBarcodeType(): string { return $this->barcodeType; }
    public function getCopies(): int { return $this->copies; }
    public function getStatus(): string { return $this->status; }
    public function getErrorMessage(): ?string { return $this->errorMessage; }
    public function getProcessedAt(): ?\DateTimeInterface { return $this->processedAt; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isFailed(): bool { return $this->status === 'failed'; }
}
