<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Contracts;

use Modules\Barcode\Domain\Entities\BarcodePrintJob;

interface BarcodePrinterDispatcherInterface
{
    public function dispatch(BarcodePrintJob $printJob): bool;
}
