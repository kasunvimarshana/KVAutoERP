<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\RepositoryInterfaces;

use Modules\Pricing\Domain\Entities\PriceList;

interface PriceListRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?PriceList;

    public function findByCode(string $code, int $tenantId): ?PriceList;

    public function findDefault(int $tenantId): ?PriceList;

    public function allByTenant(int $tenantId): array;

    public function create(array $data): PriceList;

    public function update(int $id, array $data): PriceList;

    public function delete(int $id, int $tenantId): bool;
}
