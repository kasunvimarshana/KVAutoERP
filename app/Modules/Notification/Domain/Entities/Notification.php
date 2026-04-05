<?php declare(strict_types=1);
namespace Modules\Notification\Domain\Entities;
class Notification {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $userId,
        private readonly string $channel,
        private readonly string $subject,
        private readonly string $body,
        private readonly string $status,
        private readonly ?string $errorMessage,
        private readonly ?\DateTimeInterface $sentAt,
        private readonly ?\DateTimeInterface $readAt,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getUserId(): int { return $this->userId; }
    public function getChannel(): string { return $this->channel; }
    public function getSubject(): string { return $this->subject; }
    public function getBody(): string { return $this->body; }
    public function getStatus(): string { return $this->status; }
    public function getErrorMessage(): ?string { return $this->errorMessage; }
    public function getSentAt(): ?\DateTimeInterface { return $this->sentAt; }
    public function getReadAt(): ?\DateTimeInterface { return $this->readAt; }
    public function isRead(): bool { return $this->readAt !== null; }
}
