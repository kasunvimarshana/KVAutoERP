<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\Entities;

class Permission
{
    public function __construct(
        private readonly int|string $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly ?string $module,
        private readonly ?string $description,
    ) {}

    public function getId(): int|string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getModule(): ?string { return $this->module; }
    public function getDescription(): ?string { return $this->description; }
}
