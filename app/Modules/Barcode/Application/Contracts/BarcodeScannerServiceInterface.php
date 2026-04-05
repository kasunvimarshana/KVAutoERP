<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Contracts;

interface BarcodeScannerServiceInterface
{
    /** @return array{barcode_id: int|null, data: string, symbology: string|null, found: bool} */
    public function scan(string $rawData, int $tenantId): array;
}
