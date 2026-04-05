<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Contracts;

use Modules\Pricing\Domain\Entities\Discount;

interface DiscountServiceInterface
{
    public function createDiscount(array $data): Discount;

    public function updateDiscount(int $id, array $data): Discount;

    public function deleteDiscount(int $id, int $tenantId): bool;

    public function findById(int $id, int $tenantId): Discount;

    public function allByTenant(int $tenantId): array;

    public function getApplicableDiscounts(int $tenantId, int $productId, float $orderAmount): array;

    public function applyDiscount(string $code, float $orderAmount, int $tenantId): float;
}
