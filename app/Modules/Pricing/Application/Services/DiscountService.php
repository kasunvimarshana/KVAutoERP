<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Pricing\Application\Contracts\DiscountServiceInterface;
use Modules\Pricing\Domain\Entities\Discount;
use Modules\Pricing\Domain\RepositoryInterfaces\DiscountRepositoryInterface;

class DiscountService implements DiscountServiceInterface
{
    public function __construct(
        private readonly DiscountRepositoryInterface $repository,
    ) {}

    public function create(array $data): Discount
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Discount
    {
        $discount = $this->repository->update($id, $data);

        if ($discount === null) {
            throw new NotFoundException('Discount', $id);
        }

        return $discount;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Discount
    {
        $discount = $this->repository->findById($id);

        if ($discount === null) {
            throw new NotFoundException('Discount', $id);
        }

        return $discount;
    }

    public function apply(int $discountId, float $orderAmount, array $productIds, array $categoryIds): float
    {
        $discount = $this->find($discountId);

        if (!$this->isApplicable($discount, $orderAmount, $productIds, $categoryIds)) {
            return 0.0;
        }

        return $this->calculateAmount($discount, $orderAmount);
    }

    public function validateAndApply(int $tenantId, string $code, float $orderAmount, array $productIds, array $categoryIds): float
    {
        $discount = $this->repository->findByCode($tenantId, $code);

        if ($discount === null) {
            throw new NotFoundException("Discount with code '{$code}'");
        }

        if (!$discount->isValid(new \DateTimeImmutable())) {
            return 0.0;
        }

        return $this->apply($discount->getId(), $orderAmount, $productIds, $categoryIds);
    }

    public function incrementUsage(int $discountId): void
    {
        $discount = $this->find($discountId);

        $this->repository->update($discountId, ['used_count' => $discount->getUsedCount() + 1]);
    }

    private function isApplicable(Discount $discount, float $orderAmount, array $productIds, array $categoryIds): bool
    {
        if ($discount->getMinOrderAmount() !== null && $orderAmount < $discount->getMinOrderAmount()) {
            return false;
        }

        if ($discount->getAppliesTo() === 'all') {
            return true;
        }

        if ($discount->getAppliesTo() === 'specific_products') {
            return !empty(array_intersect($productIds, $discount->getProductIds()));
        }

        if ($discount->getAppliesTo() === 'specific_categories') {
            return !empty(array_intersect($categoryIds, $discount->getCategoryIds()));
        }

        return false;
    }

    private function calculateAmount(Discount $discount, float $orderAmount): float
    {
        if ($discount->getType() === 'percentage') {
            return $orderAmount * ($discount->getValue() / 100);
        }

        if ($discount->getType() === 'fixed_amount') {
            return min($discount->getValue(), $orderAmount);
        }

        // buy_x_get_y — return zero; caller handles item-level logic
        return 0.0;
    }
}
