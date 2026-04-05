<?php declare(strict_types=1);
namespace Modules\Barcode\Infrastructure\Generators\Drivers;
use Modules\Barcode\Application\Contracts\BarcodeGeneratorInterface;
class DataMatrixDriver implements BarcodeGeneratorInterface {
    public function supports(string $type): bool { return in_array($type, ['DataMatrix','PDF417','Aztec','MaxiCode']); }
    public function generate(string $type, string $data, array $options = []): string {
        $size = $options['size'] ?? 100;
        return "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$size}\" height=\"{$size}\"><rect width=\"{$size}\" height=\"{$size}\" fill=\"#fff\"/><text x=\"5\" y=\"15\" font-size=\"8\">{$type}</text><text x=\"5\" y=\"30\" font-size=\"6\">".htmlspecialchars(substr($data,0,20))."</text></svg>";
    }
}
