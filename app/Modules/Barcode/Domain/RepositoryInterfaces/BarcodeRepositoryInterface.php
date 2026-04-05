<?php

declare(strict_types=1);

namespace Modules\Barcode\Domain\RepositoryInterfaces;

use Modules\Barcode\Domain\Entities\Barcode;

interface BarcodeRepositoryInterface
{
    public function create(array $data): Barcode;

    public function findById(int $id, int $tenantId): ?Barcode;

    public function findByData(string $data, int $tenantId): ?Barcode;

    /** @return Barcode[] */
    public function listAll(int $tenantId): array;
}
