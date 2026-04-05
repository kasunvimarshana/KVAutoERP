<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Contracts;

use Modules\Pricing\Domain\Entities\PriceList;

interface PriceListServiceInterface
{
    public function create(array $data): PriceList;

    public function update(int $id, array $data): PriceList;

    public function delete(int $id): bool;

    public function find(int $id): PriceList;

    public function setDefault(int $tenantId, string $code): PriceList;
}
