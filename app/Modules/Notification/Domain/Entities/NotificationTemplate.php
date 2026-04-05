<?php declare(strict_types=1);
namespace Modules\Notification\Domain\Entities;
class NotificationTemplate {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $event,
        private readonly string $channel,
        private readonly string $subject,
        private readonly string $body,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getEvent(): string { return $this->event; }
    public function getChannel(): string { return $this->channel; }
    public function getSubject(): string { return $this->subject; }
    public function getBody(): string { return $this->body; }
    public function isActive(): bool { return $this->isActive; }
    public function render(array $variables): string {
        $body = $this->body;
        foreach ($variables as $key => $value) {
            $body = str_replace('{{ '.$key.' }}', (string)$value, $body);
            $body = str_replace('{{'.$key.'}}', (string)$value, $body);
        }
        return $body;
    }
}
