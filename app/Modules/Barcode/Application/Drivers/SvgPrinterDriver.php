<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Drivers;

use Modules\Barcode\Application\Contracts\PrinterDriverInterface;
use Modules\Barcode\Domain\Entities\BarcodePrintJob;

class SvgPrinterDriver implements PrinterDriverInterface
{
    public function supports(string $format): bool
    {
        return strtolower($format) === 'svg';
    }

    public function print(BarcodePrintJob $job, string $renderedLabel): bool
    {
        // SVG printing implementation: render SVG to PDF / image and send to printer
        // In production this would use a headless browser or image conversion library
        return true;
    }
}
