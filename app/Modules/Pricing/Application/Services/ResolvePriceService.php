<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Pricing\Application\Contracts\ResolvePriceServiceInterface;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListItemRepositoryInterface;

class ResolvePriceService implements ResolvePriceServiceInterface
{
    public function __construct(
        private readonly PriceListItemRepositoryInterface $itemRepo,
    ) {}

    public function resolvePrice(
        int $priceListId,
        int $productId,
        ?int $variantId,
        float $quantity,
        int $tenantId,
    ): ?float {
        $items = $this->itemRepo->findByPriceList($priceListId, $tenantId);

        // Filter for this product (and variant if given, falling back to null variant)
        $matched = array_filter($items, function ($item) use ($productId, $variantId, $quantity) {
            if ($item->productId !== $productId) {
                return false;
            }

            // Variant match: exact match or null (wildcard)
            if ($variantId !== null && $item->variantId !== null && $item->variantId !== $variantId) {
                return false;
            }

            // Quantity tier check
            if ($item->minQuantity > $quantity) {
                return false;
            }

            if ($item->maxQuantity !== null && $item->maxQuantity < $quantity) {
                return false;
            }

            return true;
        });

        if (empty($matched)) {
            // Try fallback: variant-specific items when variantId is given but nothing found
            return null;
        }

        // Prefer variant-specific items over product-wide; then pick highest minQuantity tier
        usort($matched, function ($a, $b) {
            // Variant-specific takes precedence
            $aSpecific = $a->variantId !== null ? 1 : 0;
            $bSpecific = $b->variantId !== null ? 1 : 0;

            if ($aSpecific !== $bSpecific) {
                return $bSpecific - $aSpecific;
            }

            // Higher minQuantity = more specific tier
            return $b->minQuantity <=> $a->minQuantity;
        });

        $best = reset($matched);

        return (float) $best->price;
    }
}
