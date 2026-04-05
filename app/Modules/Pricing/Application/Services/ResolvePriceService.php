<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Pricing\Application\Contracts\ResolvePriceServiceInterface;
use Modules\Pricing\Domain\Entities\PriceListItem;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListItemRepositoryInterface;

class ResolvePriceService implements ResolvePriceServiceInterface
{
    public function __construct(
        private readonly PriceListItemRepositoryInterface $itemRepository,
    ) {}

    public function resolve(
        int $priceListId,
        int $productId,
        ?int $variantId,
        float $quantity,
        float $basePrice,
    ): float {
        $items = $this->itemRepository->findByProduct($priceListId, $productId, $variantId);

        // Filter active items and find the best matching quantity tier
        $matchingItem = null;
        $bestMin      = -1.0;

        foreach ($items as $item) {
            if (!$item->isActive()) {
                continue;
            }

            if ($quantity < $item->getMinQuantity()) {
                continue;
            }

            if ($item->getMaxQuantity() !== null && $quantity > $item->getMaxQuantity()) {
                continue;
            }

            // Prefer the tier with the highest minQuantity (most specific)
            if ($item->getMinQuantity() > $bestMin) {
                $bestMin      = $item->getMinQuantity();
                $matchingItem = $item;
            }
        }

        if ($matchingItem === null) {
            return $basePrice;
        }

        return $matchingItem->calculatePrice($basePrice, $quantity);
    }
}
