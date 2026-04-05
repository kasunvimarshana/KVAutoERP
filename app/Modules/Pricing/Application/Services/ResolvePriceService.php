<?php declare(strict_types=1);
namespace Modules\Pricing\Application\Services;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface;
class ResolvePriceService {
    public function __construct(private readonly PriceListRepositoryInterface $repo) {}
    public function resolve(int $priceListId, int $productId, float $quantity, ?\DateTimeInterface $date = null): ?float {
        $date = $date ?? new \DateTimeImmutable();
        $items = $this->repo->findItemsByProduct($priceListId, $productId);
        // Filter by validity and min quantity, sort by highest tier (largest minQty <= requested qty)
        $eligible = array_filter($items, fn($i) => $i->isValidOn($date) && $i->getMinQuantity() <= $quantity);
        if (empty($eligible)) return null;
        usort($eligible, fn($a,$b) => $b->getMinQuantity() <=> $a->getMinQuantity());
        return $eligible[0]->getPrice();
    }
}
