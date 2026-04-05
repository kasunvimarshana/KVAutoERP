<?php declare(strict_types=1);
namespace Modules\Barcode\Domain\Events;
class BarcodeScanRecorded {
    public function __construct(
        public readonly string $barcodeData,
        public readonly string $barcodeType,
        public readonly int $tenantId,
        public readonly ?int $userId,
        public readonly \DateTimeInterface $scannedAt,
    ) {}
}
