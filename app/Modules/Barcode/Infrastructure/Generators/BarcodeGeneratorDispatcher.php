<?php declare(strict_types=1);
namespace Modules\Barcode\Infrastructure\Generators;
use Modules\Barcode\Application\Contracts\BarcodeGeneratorInterface;
class BarcodeGeneratorDispatcher {
    /** @var BarcodeGeneratorInterface[] */
    private array $generators = [];
    public function register(BarcodeGeneratorInterface $generator): void { $this->generators[] = $generator; }
    public function generate(string $type, string $data, array $options = []): string {
        foreach ($this->generators as $generator) {
            if ($generator->supports($type)) {
                return $generator->generate($type, $data, $options);
            }
        }
        throw new \InvalidArgumentException("No generator registered for barcode type: {$type}");
    }
}
