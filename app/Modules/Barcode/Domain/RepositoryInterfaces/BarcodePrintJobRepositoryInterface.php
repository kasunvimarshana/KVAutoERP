<?php

declare(strict_types=1);

namespace Modules\Barcode\Domain\RepositoryInterfaces;

use Modules\Barcode\Domain\Entities\BarcodePrintJob;

interface BarcodePrintJobRepositoryInterface
{
    public function create(array $data): BarcodePrintJob;

    public function update(int $id, array $data): BarcodePrintJob;

    public function findById(int $id, int $tenantId): ?BarcodePrintJob;

    /** @return BarcodePrintJob[] */
    public function listAll(int $tenantId): array;
}
