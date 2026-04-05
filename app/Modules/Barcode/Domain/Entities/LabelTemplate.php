<?php declare(strict_types=1);
namespace Modules\Barcode\Domain\Entities;
class LabelTemplate {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $format,
        private readonly string $content,
        private readonly int $width,
        private readonly int $height,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getFormat(): string { return $this->format; }
    public function getContent(): string { return $this->content; }
    public function getWidth(): int { return $this->width; }
    public function getHeight(): int { return $this->height; }
    public function isActive(): bool { return $this->isActive; }
    public function render(array $variables): string {
        $content = $this->content;
        foreach ($variables as $key => $value) {
            $content = str_replace('{{ '.$key.' }}', (string)$value, $content);
            $content = str_replace('{{'.$key.'}}', (string)$value, $content);
        }
        return $content;
    }
}
