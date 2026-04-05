<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Contracts;

use Modules\Barcode\Domain\Entities\Barcode;

interface BarcodeGeneratorServiceInterface
{
    public function generate(string $symbology, string $data, int $tenantId): Barcode;
}
