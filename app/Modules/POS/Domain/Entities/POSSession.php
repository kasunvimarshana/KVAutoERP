<?php declare(strict_types=1);
namespace Modules\POS\Domain\Entities;
class POSSession {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $terminalId,
        private readonly int $userId,
        private readonly float $openingCash,
        private readonly ?float $closingCash,
        private readonly string $status,
        private readonly \DateTimeInterface $openedAt,
        private readonly ?\DateTimeInterface $closedAt,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getTerminalId(): int { return $this->terminalId; }
    public function getUserId(): int { return $this->userId; }
    public function getOpeningCash(): float { return $this->openingCash; }
    public function getClosingCash(): ?float { return $this->closingCash; }
    public function getStatus(): string { return $this->status; }
    public function getOpenedAt(): \DateTimeInterface { return $this->openedAt; }
    public function getClosedAt(): ?\DateTimeInterface { return $this->closedAt; }
    public function isOpen(): bool { return $this->status === 'open'; }
}
