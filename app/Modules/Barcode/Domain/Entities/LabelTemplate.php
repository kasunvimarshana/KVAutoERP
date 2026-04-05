<?php

declare(strict_types=1);

namespace Modules\Barcode\Domain\Entities;

class LabelTemplate
{
    public function __construct(
        private readonly int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $format,
        private readonly string $template,
        private readonly ?float $width,
        private readonly ?float $height,
        private readonly array $variables,
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function render(array $data): string
    {
        $output = $this->template;

        foreach ($data as $key => $value) {
            $output = str_replace('{{ '.$key.' }}', (string) $value, $output);
            $output = str_replace('{{'.$key.'}}', (string) $value, $output);
        }

        return $output;
    }
}
