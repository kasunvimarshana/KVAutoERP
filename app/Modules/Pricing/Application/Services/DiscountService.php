<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Pricing\Application\Contracts\DiscountServiceInterface;
use Modules\Pricing\Domain\Entities\Discount;
use Modules\Pricing\Domain\RepositoryInterfaces\DiscountRepositoryInterface;

class DiscountService implements DiscountServiceInterface
{
    public function __construct(
        private readonly DiscountRepositoryInterface $repo,
    ) {}

    public function createDiscount(array $data): Discount
    {
        $tenantId = (int) $data['tenant_id'];
        $code     = (string) $data['code'];

        if ($this->repo->findByCode($code, $tenantId) !== null) {
            throw new \InvalidArgumentException("Discount code '{$code}' already exists for this tenant.");
        }

        if (!in_array($data['type'] ?? '', ['percentage', 'fixed'], true)) {
            throw new \InvalidArgumentException("Discount type must be 'percentage' or 'fixed'.");
        }

        if ((float) ($data['value'] ?? 0) <= 0) {
            throw new \InvalidArgumentException('Discount value must be greater than zero.');
        }

        return $this->repo->create($data);
    }

    public function updateDiscount(int $id, array $data): Discount
    {
        $tenantId = (int) ($data['tenant_id'] ?? 0);
        $discount = $this->repo->findById($id, $tenantId);

        if ($discount === null) {
            throw new \InvalidArgumentException("Discount with id {$id} not found.");
        }

        if (isset($data['code']) && $data['code'] !== $discount->code) {
            $existing = $this->repo->findByCode((string) $data['code'], $tenantId);
            if ($existing !== null && $existing->id !== $id) {
                throw new \InvalidArgumentException("Discount code '{$data['code']}' already exists for this tenant.");
            }
        }

        if (isset($data['type']) && !in_array($data['type'], ['percentage', 'fixed'], true)) {
            throw new \InvalidArgumentException("Discount type must be 'percentage' or 'fixed'.");
        }

        return $this->repo->update($id, $data);
    }

    public function deleteDiscount(int $id, int $tenantId): bool
    {
        $discount = $this->repo->findById($id, $tenantId);

        if ($discount === null) {
            throw new \InvalidArgumentException("Discount with id {$id} not found.");
        }

        return $this->repo->delete($id, $tenantId);
    }

    public function findById(int $id, int $tenantId): Discount
    {
        $discount = $this->repo->findById($id, $tenantId);

        if ($discount === null) {
            throw new \InvalidArgumentException("Discount with id {$id} not found.");
        }

        return $discount;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->repo->allByTenant($tenantId);
    }

    public function getApplicableDiscounts(int $tenantId, int $productId, float $orderAmount): array
    {
        $active = $this->repo->findActive($tenantId);
        $now    = new \DateTimeImmutable();

        return array_filter($active, function (Discount $d) use ($productId, $orderAmount, $now) {
            // Validity window
            if ($d->validFrom !== null && $d->validFrom > $now) {
                return false;
            }
            if ($d->validTo !== null && $d->validTo < $now) {
                return false;
            }

            // Usage limit
            if ($d->usageLimit !== null && $d->usageCount >= $d->usageLimit) {
                return false;
            }

            // Min order amount
            if ($d->minOrderAmount !== null && $orderAmount < $d->minOrderAmount) {
                return false;
            }

            // Applicability
            if ($d->appliesToType === 'product' && $d->appliesToId !== $productId) {
                return false;
            }

            return true;
        });
    }

    public function applyDiscount(string $code, float $orderAmount, int $tenantId): float
    {
        $discount = $this->repo->findByCode($code, $tenantId);

        if ($discount === null) {
            throw new \InvalidArgumentException("Discount code '{$code}' not found.");
        }

        if (!$discount->isActive) {
            throw new \RuntimeException("Discount code '{$code}' is not active.");
        }

        $now = new \DateTimeImmutable();

        if ($discount->validFrom !== null && $discount->validFrom > $now) {
            throw new \RuntimeException("Discount code '{$code}' is not yet valid.");
        }

        if ($discount->validTo !== null && $discount->validTo < $now) {
            throw new \RuntimeException("Discount code '{$code}' has expired.");
        }

        if ($discount->usageLimit !== null && $discount->usageCount >= $discount->usageLimit) {
            throw new \RuntimeException("Discount code '{$code}' has reached its usage limit.");
        }

        if ($discount->minOrderAmount !== null && $orderAmount < $discount->minOrderAmount) {
            throw new \RuntimeException(
                "Order amount does not meet the minimum required for discount code '{$code}'."
            );
        }

        $finalAmount = match ($discount->type) {
            'percentage' => $orderAmount - ($orderAmount * $discount->value / 100),
            'fixed'      => max(0.0, $orderAmount - $discount->value),
            default      => throw new \RuntimeException("Unknown discount type '{$discount->type}'."),
        };

        $this->repo->incrementUsage($discount->id);

        return round($finalAmount, 4);
    }
}
