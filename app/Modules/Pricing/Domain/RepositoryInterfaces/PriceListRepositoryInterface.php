<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\RepositoryInterfaces;

use Modules\Pricing\Domain\Entities\PriceList;

interface PriceListRepositoryInterface
{
    public function findById(int $id): ?PriceList;

    public function findByCode(int $tenantId, string $code): ?PriceList;

    public function findDefault(int $tenantId): ?PriceList;

    /** @return PriceList[] */
    public function findActive(int $tenantId): array;

    public function create(array $data): PriceList;

    public function update(int $id, array $data): ?PriceList;

    public function delete(int $id): bool;
}
