<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Drivers;

use Modules\Barcode\Application\Contracts\PrinterDriverInterface;
use Modules\Barcode\Domain\Entities\BarcodePrintJob;

class ZplPrinterDriver implements PrinterDriverInterface
{
    public function supports(string $format): bool
    {
        return strtolower($format) === 'zpl';
    }

    public function print(BarcodePrintJob $job, string $renderedLabel): bool
    {
        // ZPL printing implementation: send to printer via network socket or spool
        // In production this would open a TCP connection to the ZPL-capable printer
        return true;
    }
}
