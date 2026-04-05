<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Contracts;

use Modules\Barcode\Domain\Entities\BarcodePrintJob;

interface PrinterDriverInterface
{
    public function supports(string $format): bool;

    public function print(BarcodePrintJob $job, string $renderedLabel): bool;
}
