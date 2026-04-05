<?php declare(strict_types=1);
namespace Modules\Barcode\Domain\Entities;
class BarcodeSymbology {
    public const SUPPORTED = [
        'EAN13','EAN8','UPC-A','UPC-E','Code39','Code93','Code128',
        'ITF','Codabar','PDF417','DataMatrix','QrCode','Aztec','MaxiCode','GS1-128'
    ];
    public function __construct(
        private readonly string $type,
        private readonly string $data,
        private readonly ?int $width,
        private readonly ?int $height,
        private readonly string $format,
    ) {
        if (!in_array($type, self::SUPPORTED)) {
            throw new \InvalidArgumentException("Unsupported barcode type: {$type}");
        }
    }
    public function getType(): string { return $this->type; }
    public function getData(): string { return $this->data; }
    public function getWidth(): ?int { return $this->width; }
    public function getHeight(): ?int { return $this->height; }
    public function getFormat(): string { return $this->format; }
    public function is2D(): bool { return in_array($this->type, ['PDF417','DataMatrix','QrCode','Aztec','MaxiCode']); }
    public function is1D(): bool { return !$this->is2D(); }
}
