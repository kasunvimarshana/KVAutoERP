<?php

declare(strict_types=1);

namespace Modules\Barcode\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BarcodeScanRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $barcodeId,
        public readonly string $data,
        public readonly \DateTimeInterface $scannedAt,
        public readonly ?int $userId,
    ) {}
}
