<?php declare(strict_types=1);
namespace Modules\Asset\Domain\Entities;

class AssetDepreciation
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $assetId,
        private readonly int $tenantId,
        private readonly \DateTimeInterface $periodDate,
        private readonly float $amount,
        private readonly float $bookValueBefore,
        private readonly float $bookValueAfter,
        private readonly ?int $journalEntryId,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getAssetId(): int { return $this->assetId; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getPeriodDate(): \DateTimeInterface { return $this->periodDate; }
    public function getAmount(): float { return $this->amount; }
    public function getBookValueBefore(): float { return $this->bookValueBefore; }
    public function getBookValueAfter(): float { return $this->bookValueAfter; }
    public function getJournalEntryId(): ?int { return $this->journalEntryId; }
}
