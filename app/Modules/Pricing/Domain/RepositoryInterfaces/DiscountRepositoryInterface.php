<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\RepositoryInterfaces;

use Modules\Pricing\Domain\Entities\Discount;

interface DiscountRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Discount;

    public function findByCode(string $code, int $tenantId): ?Discount;

    public function findActive(int $tenantId): array;

    public function allByTenant(int $tenantId): array;

    public function create(array $data): Discount;

    public function update(int $id, array $data): Discount;

    public function delete(int $id, int $tenantId): bool;

    public function incrementUsage(int $id): void;
}
