<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Contracts;

use Modules\Pricing\Domain\Entities\PriceList;

interface PriceListServiceInterface
{
    public function create(array $data): PriceList;

    public function update(int $id, array $data): PriceList;

    public function delete(int $id, int $tenantId): bool;

    public function findById(int $id, int $tenantId): PriceList;

    public function allByTenant(int $tenantId): array;

    public function getDefault(int $tenantId): ?PriceList;

    public function setDefault(int $id, int $tenantId): PriceList;
}
