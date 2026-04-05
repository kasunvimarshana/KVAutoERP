<?php declare(strict_types=1);
namespace Modules\Barcode\Infrastructure\Generators\Drivers;
use Modules\Barcode\Application\Contracts\BarcodeGeneratorInterface;
class SvgBarcodeDriver implements BarcodeGeneratorInterface {
    private const SUPPORTED = ['EAN13','EAN8','UPC-A','UPC-E','Code39','Code93','Code128','ITF','Codabar','GS1-128'];
    public function supports(string $type): bool { return in_array($type, self::SUPPORTED); }
    public function generate(string $type, string $data, array $options = []): string {
        $width = $options['width'] ?? 200;
        $height = $options['height'] ?? 80;
        $barCount = strlen($data) * 4;
        $barWidth = max(1, (int)($width / $barCount));
        $bars = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $ord = ord($data[$i]);
            for ($b = 0; $b < 4; $b++) {
                $x = ($i * 4 + $b) * $barWidth;
                $fill = (($ord >> $b) & 1) === 1 ? '#000000' : '#ffffff';
                $bars .= "<rect x=\"{$x}\" y=\"0\" width=\"{$barWidth}\" height=\"{$height}\" fill=\"{$fill}\"/>";
            }
        }
        return "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$width}\" height=\"{$height}\">{$bars}<text x=\"0\" y=\"".($height+12)."\" font-size=\"10\">{$data}</text></svg>";
    }
}
