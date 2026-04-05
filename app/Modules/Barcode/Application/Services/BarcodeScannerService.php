<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Services;

use Modules\Barcode\Application\Contracts\BarcodeScannerServiceInterface;
use Modules\Barcode\Domain\Events\BarcodeScanRecorded;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodeRepositoryInterface;

class BarcodeScannerService implements BarcodeScannerServiceInterface
{
    public function __construct(
        private readonly BarcodeRepositoryInterface $repository,
    ) {}

    public function scan(string $rawData, int $tenantId): array
    {
        $barcode = $this->repository->findByData($rawData, $tenantId);

        $result = [
            'barcode_id' => $barcode?->getId(),
            'data'       => $rawData,
            'symbology'  => $barcode?->getSymbology(),
            'found'      => $barcode !== null,
        ];

        BarcodeScanRecorded::dispatch(
            barcodeId: $barcode?->getId() ?? 0,
            data: $rawData,
            scannedAt: new \DateTimeImmutable(),
            userId: auth()->id(),
        );

        return $result;
    }
}
