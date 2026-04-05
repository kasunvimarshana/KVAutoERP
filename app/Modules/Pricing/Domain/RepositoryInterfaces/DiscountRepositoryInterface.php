<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\RepositoryInterfaces;

use Modules\Pricing\Domain\Entities\Discount;

interface DiscountRepositoryInterface
{
    public function findById(int $id): ?Discount;

    public function findByCode(int $tenantId, string $code): ?Discount;

    /** @return Discount[] */
    public function findActive(int $tenantId, \DateTimeInterface $date): array;

    public function create(array $data): Discount;

    public function update(int $id, array $data): ?Discount;

    public function delete(int $id): bool;
}
