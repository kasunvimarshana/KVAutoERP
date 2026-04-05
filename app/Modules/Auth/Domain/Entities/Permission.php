<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

final class Permission
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $module,
        private readonly ?string $description = null,
    ) {}

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getModule(): string { return $this->module; }
    public function getDescription(): ?string { return $this->description; }
}
