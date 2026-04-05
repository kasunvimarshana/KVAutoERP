<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Services;

use Modules\Barcode\Application\Contracts\BarcodePrinterDispatcherInterface;
use Modules\Barcode\Application\Contracts\LabelTemplateServiceInterface;
use Modules\Barcode\Application\Contracts\PrinterDriverInterface;
use Modules\Barcode\Domain\Entities\BarcodePrintJob;
use Modules\Core\Domain\Exceptions\DomainException;

class BarcodePrinterDispatcher implements BarcodePrinterDispatcherInterface
{
    /** @param PrinterDriverInterface[] $drivers */
    public function __construct(
        private readonly LabelTemplateServiceInterface $labelTemplateService,
        private readonly array $drivers = [],
    ) {}

    public function dispatch(BarcodePrintJob $printJob): bool
    {
        $templateId = $printJob->getLabelTemplateId();
        $tenantId   = $printJob->getTenantId();

        if ($templateId === null) {
            throw new DomainException('Print job has no label template assigned.');
        }

        $template = $this->labelTemplateService->findById($templateId, $tenantId);

        if ($template === null) {
            throw new DomainException("LabelTemplate #{$templateId} not found.");
        }

        $rendered = $template->render([]);

        foreach ($this->drivers as $driver) {
            if ($driver->supports($template->getFormat())) {
                return $driver->print($printJob, $rendered);
            }
        }

        throw new DomainException("No printer driver found for format: {$template->getFormat()}");
    }
}
