<?php declare(strict_types=1);
namespace Modules\Tenant\Domain\Entities;
class Tenant {
    public function __construct(
        private readonly ?int $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $plan, // free|starter|professional|enterprise
        private readonly bool $isActive,
        private readonly ?array $settings,
        private readonly ?\DateTimeInterface $trialEndsAt,
        private readonly ?\DateTimeInterface $createdAt,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getPlan(): string { return $this->plan; }
    public function isActive(): bool { return $this->isActive; }
    public function getSettings(): ?array { return $this->settings; }
    public function getTrialEndsAt(): ?\DateTimeInterface { return $this->trialEndsAt; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
}
