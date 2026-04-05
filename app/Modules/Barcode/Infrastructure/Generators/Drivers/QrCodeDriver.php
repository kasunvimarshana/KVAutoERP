<?php declare(strict_types=1);
namespace Modules\Barcode\Infrastructure\Generators\Drivers;
use Modules\Barcode\Application\Contracts\BarcodeGeneratorInterface;
class QrCodeDriver implements BarcodeGeneratorInterface {
    private const SUPPORTED = ['QrCode'];
    public function supports(string $type): bool { return in_array($type, self::SUPPORTED); }
    public function generate(string $type, string $data, array $options = []): string {
        $size = $options['size'] ?? 100;
        $checksum = crc32($data);
        $cells = abs($checksum) % 10 + 15;
        $cellSize = (int)($size / $cells);
        $squares = '';
        for ($r = 0; $r < $cells; $r++) {
            for ($c = 0; $c < $cells; $c++) {
                $bit = ($checksum >> (($r * $cells + $c) % 32)) & 1;
                if ($bit) {
                    $x = $c * $cellSize;
                    $y = $r * $cellSize;
                    $squares .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$cellSize}\" height=\"{$cellSize}\" fill=\"#000\"/>";
                }
            }
        }
        return "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 {$size} {$size}\">{$squares}</svg>";
    }
}
