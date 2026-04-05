<?php declare(strict_types=1);
namespace Modules\Barcode\Application\Contracts;
interface BarcodeGeneratorInterface {
    public function generate(string $type, string $data, array $options = []): string;
    public function supports(string $type): bool;
}
