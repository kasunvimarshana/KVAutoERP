<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Contracts;

use Modules\Pricing\Domain\Entities\Discount;

interface DiscountServiceInterface
{
    public function create(array $data): Discount;

    public function update(int $id, array $data): Discount;

    public function delete(int $id): bool;

    public function find(int $id): Discount;

    public function apply(int $discountId, float $orderAmount, array $productIds, array $categoryIds): float;

    public function validateAndApply(int $tenantId, string $code, float $orderAmount, array $productIds, array $categoryIds): float;

    public function incrementUsage(int $discountId): void;
}
